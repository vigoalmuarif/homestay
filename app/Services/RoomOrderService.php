<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\RoomTypeServiceInclusion;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RoomOrderService
{
    /**
     * Calculate package inclusion allowance for a service against the booking room type.
     */
    public function calculateInclusion(Booking $booking, Service $service, int $quantity): array
    {
        $roomTypeId = $booking->room?->room_type_id;
        $unitPrice = (float) $service->price;
        $totalOriginal = $unitPrice * $quantity;

        if (!$roomTypeId) {
            return [
                'is_free_inclusion' => false,
                'unit_price' => $unitPrice,
                'discount_or_allowance' => 0.00,
                'subtotal' => $totalOriginal,
                'inclusion_applied' => null,
            ];
        }

        // Check for specific service inclusion or category matching inclusion
        $inclusions = RoomTypeServiceInclusion::where('room_type_id', $roomTypeId)
            ->where(function ($q) use ($service) {
                $q->where('service_id', $service->id)
                    ->orWhere(function ($sub) use ($service) {
                        $sub->whereNull('service_id')
                            ->whereIn('category', [$service->category, 'any']);
                    });
            })
            ->get();

        $inclusion = $inclusions->sortByDesc(function ($inc) use ($service) {
            if ($inc->service_id === $service->id) return 100;
            if ($inc->session_time && $service->sub_category && $inc->session_time === $service->sub_category) return 50;
            if ($inc->category === $service->category) return 10;
            return 1;
        })->first(function ($inc) use ($service) {
            if ($inc->service_id) {
                return $inc->service_id === $service->id;
            }
            if ($inc->category !== 'any' && $inc->category !== $service->category) {
                return false;
            }
            if ($inc->session_time && $service->sub_category) {
                return $inc->session_time === $service->sub_category;
            }
            return true;
        });

        if (!$inclusion) {
            return [
                'is_free_inclusion' => false,
                'unit_price' => $unitPrice,
                'discount_or_allowance' => 0.00,
                'subtotal' => $totalOriginal,
                'inclusion_applied' => null,
                'quota_exhausted' => false,
            ];
        }

        // Daily / session-based or per-stay entitlement check
        $isDailyEntitlement = in_array($inclusion->session_time, ['breakfast', 'daily', 'lunch', 'dinner'], true);

        $alreadyClaimedQuery = BookingItem::where('booking_id', $booking->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->where('is_free_inclusion', true)
                    ->orWhere('discount_or_allowance', '>', 0);
            })
            ->where(function ($q) use ($inclusion) {
                if ($inclusion->service_id) {
                    $q->where('service_id', $inclusion->service_id);
                } else {
                    $q->whereHas('service', function ($sub) use ($inclusion) {
                        $sub->where('category', $inclusion->category);
                        if ($inclusion->session_time) {
                            $sub->where(function ($sub2) use ($inclusion) {
                                $sub2->where('sub_category', $inclusion->session_time)
                                     ->orWhereNull('sub_category');
                            });
                        }
                    });
                }
            });

        if ($isDailyEntitlement) {
            $alreadyClaimedQuery->whereDate('created_at', now()->toDateString());
        }

        if ($alreadyClaimedQuery->exists()) {
            // Daily quota already claimed today: fallback to standard billing without blocking order
            return [
                'is_free_inclusion' => false,
                'unit_price' => $unitPrice,
                'discount_or_allowance' => 0.00,
                'subtotal' => $totalOriginal,
                'inclusion_applied' => $inclusion,
                'quota_exhausted' => true,
            ];
        }

        $allowanceLimit = (float) $inclusion->allowance_limit;

        if ($allowanceLimit <= 0.0) {
            // 0.00 indicates full coverage (e.g. Free Breakfast package)
            return [
                'is_free_inclusion' => true,
                'unit_price' => $unitPrice,
                'discount_or_allowance' => $totalOriginal,
                'subtotal' => 0.00,
                'inclusion_applied' => $inclusion,
            ];
        }

        // Partial allowance cap
        $discount = min($allowanceLimit, $totalOriginal);
        $subtotal = max(0.00, $totalOriginal - $discount);

        return [
            'is_free_inclusion' => ($subtotal == 0.00),
            'unit_price' => $unitPrice,
            'discount_or_allowance' => $discount,
            'subtotal' => $subtotal,
            'inclusion_applied' => $inclusion,
        ];
    }

    /**
     * Atomically creates a room order, validates and decrements physical stock,
     * applies inclusion benefits, and registers the booking item.
     *
     * @throws RuntimeException
     */
    public function createOrder(Booking $booking, array $data, ?int $userId = null): BookingItem
    {
        return DB::transaction(function () use ($booking, $data, $userId) {
            $serviceId = (int) $data['service_id'];
            $quantity = max(1, (int) ($data['quantity'] ?? 1));
            $billingType = $data['billing_type'] ?? 'charged_to_room';
            $notes = $data['notes'] ?? null;

            // Fetch and lock service for atomic inventory check
            $service = Service::where('id', $serviceId)->lockForUpdate()->firstOrFail();

            if (!$service->is_active) {
                throw new RuntimeException("Layanan '{$service->name}' sedang tidak aktif.");
            }

            // Atomic stock check and deduction
            if ($service->has_stock) {
                if ($service->stock_quantity < $quantity) {
                    throw new RuntimeException("Stok {$service->name} tidak mencukupi (Tersisa: {$service->stock_quantity}, Diminta: {$quantity}).");
                }

                $service->decrement('stock_quantity', $quantity);
            }

            // Calculate inclusion allowance
            $calculation = $this->calculateInclusion($booking, $service, $quantity);

            // If user explicitly attempts to claim an inclusion but quota is already exhausted, reject with validation error
            if (!empty($calculation['quota_exhausted']) && $notes && str_contains(strtolower($notes), 'klaim inklusi')) {
                throw new RuntimeException("Hak inklusi '{$service->name}' untuk hari ini sudah pernah diklaim sebelumnya. Kuota benefit hari ini telah habis.");
            }

            return BookingItem::create([
                'booking_id' => $booking->id,
                'service_id' => $service->id,
                'item_name' => $service->name,
                'quantity' => $quantity,
                'unit_price' => $calculation['unit_price'],
                'discount_or_allowance' => $calculation['discount_or_allowance'],
                'subtotal' => $calculation['subtotal'],
                'billing_type' => $billingType,
                'status' => 'delivered',
                'is_free_inclusion' => $calculation['is_free_inclusion'],
                'notes' => $notes,
                'served_by' => $userId,
            ]);
        });
    }

    /**
     * Cancel an existing room order item and restore physical stock if applicable.
     */
    public function cancelOrder(BookingItem $bookingItem): BookingItem
    {
        return DB::transaction(function () use ($bookingItem) {
            if ($bookingItem->status === 'cancelled') {
                return $bookingItem;
            }

            // Restore physical stock if applicable
            $service = Service::where('id', $bookingItem->service_id)->lockForUpdate()->first();
            if ($service && $service->has_stock) {
                $service->increment('stock_quantity', $bookingItem->quantity);
            }

            $bookingItem->status = 'cancelled';
            $bookingItem->save();

            return $bookingItem;
        });
    }
}

