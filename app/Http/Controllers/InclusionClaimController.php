<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\RoomTypeServiceInclusion;
use App\Models\Service;
use App\Services\RoomOrderService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InclusionClaimController extends Controller
{
    /**
     * Display the daily inclusion monitoring and claim fulfillment desk.
     */
    public function index(Request $request): View
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = Carbon::today()->toDateString();
        }

        $isToday = ($selectedDate === Carbon::today()->toDateString());
        $categoryFilter = $request->input('category', 'all');
        $search = trim($request->input('search', ''));

        // 1. Fetch Bookings active on the selected date with room type inclusions and items on that date
        $bookingsQuery = Booking::query();

        if ($isToday) {
            $bookingsQuery->inHouse();
        } else {
            $bookingsQuery->where(function ($q) use ($selectedDate) {
                $q->whereDate('check_in_date', '<=', $selectedDate)
                  ->whereDate('check_out_date', '>=', $selectedDate);
            })->whereIn('status', ['checked_in', 'checked_out']);
        }

        $bookingsQuery->with([
            'customer',
            'room.roomType.inclusions.service',
            'items' => function ($q) use ($selectedDate) {
                $q->whereDate('created_at', $selectedDate)
                  ->where('status', '!=', 'cancelled')
                  ->with('service');
            },
        ]);

        if (!empty($search)) {
            $bookingsQuery->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('phone_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('room', function ($rq) use ($search) {
                      $rq->where('room_number', 'like', "%{$search}%");
                  });
            });
        }

        $allInHouseBookings = $bookingsQuery->get();

        // 2. Prepare Guest Inclusions Roster
        $guestInclusionList = [];
        $totalEntitledPax = 0;
        $totalClaimedToday = 0;
        $totalPendingClaims = 0;

        foreach ($allInHouseBookings as $booking) {
            $inclusions = $booking->room?->roomType?->inclusions ?? collect();

            if ($inclusions->isEmpty()) {
                continue;
            }

            foreach ($inclusions as $inc) {
                if ($categoryFilter !== 'all' && $inc->category !== $categoryFilter) {
                    continue;
                }

                $totalEntitledPax++;

                // Check if this inclusion has been claimed on the selected date in booking_items
                $claimedItem = $booking->items->first(function ($item) use ($inc) {
                    if (!$item->is_free_inclusion && $item->discount_or_allowance <= 0) {
                        return false;
                    }
                    if ($inc->service_id && $item->service_id === $inc->service_id) {
                        return true;
                    }
                    if ($inc->category === 'any') {
                        return true;
                    }
                    return $item->service?->category === $inc->category;
                });

                $isClaimed = $claimedItem !== null;
                if ($isClaimed) {
                    $totalClaimedToday++;
                } else {
                    $totalPendingClaims++;
                }

                $guestInclusionList[] = [
                    'booking' => $booking,
                    'inclusion' => $inc,
                    'is_claimed' => $isClaimed,
                    'claimed_item' => $claimedItem,
                    'category' => $inc->category,
                    'session_time' => $inc->session_time,
                ];
            }
        }

        // 3. Fetch active fulfillment tickets (Kitchen, Laundry, Rental orders in progress on selected date)
        $activeOrders = BookingItem::whereDate('created_at', $selectedDate)
            ->where('status', '!=', 'cancelled')
            ->with(['booking.room', 'booking.customer', 'service', 'servedBy'])
            ->latest()
            ->get();

        $activeFulfillmentCount = $activeOrders->where('status', 'pending')->count();

        // 4. Available Services categorized for the claim popup modals
        $availableServices = Service::active()->orderBy('category')->orderBy('name')->get();

        return view('pages.services.claim-desk', [
            'title' => 'Pusat Klaim Inklusi & Fulfillment',
            'guestInclusionList' => $guestInclusionList,
            'activeOrders' => $activeOrders,
            'availableServices' => $availableServices,
            'stats' => [
                'in_house_count' => $allInHouseBookings->count(),
                'total_entitled' => $totalEntitledPax,
                'total_claimed' => $totalClaimedToday,
                'total_unclaimed' => $totalPendingClaims,
                'active_fulfillment' => $activeFulfillmentCount,
            ],
            'currentFilter' => $categoryFilter,
            'search' => $search,
            'selectedDate' => $selectedDate,
            'isToday' => $isToday,
        ]);
    }

    /**
     * 1-Click Quick Claim (Instant buffet breakfast, welcome drink, or immediate amenity).
     */
    public function quickClaim(Request $request, Booking $booking, RoomOrderService $orderService): RedirectResponse
    {
        $inclusionId = $request->input('inclusion_id');
        $inclusion = RoomTypeServiceInclusion::find($inclusionId);

        if (!$inclusion) {
            return back()->withErrors(['error' => 'Data paket inklusi tidak ditemukan.']);
        }

        // Determine which service to link to this item
        $service = null;
        if ($inclusion->service_id) {
            $service = Service::find($inclusion->service_id);
        } else {
            // Pick default service matching the category or create an automatic entry
            $service = Service::where('category', $inclusion->category)->first()
                ?? Service::first();
        }

        if (!$service) {
            return back()->withErrors(['error' => 'Layanan master untuk kategori ini belum tersedia.']);
        }

        $claimDate = $request->input('claim_date');
        $claimTimestamp = $claimDate ? Carbon::parse($claimDate)->setTimeFrom(now()) : now();

        try {
            DB::transaction(function () use ($booking, $service, $inclusion, $claimTimestamp) {
                BookingItem::create([
                    'booking_id' => $booking->id,
                    'service_id' => $service->id,
                    'item_name' => $service->name . ' (Inklusi ' . ucfirst($inclusion->category) . ')',
                    'quantity' => 1,
                    'unit_price' => $service->price,
                    'discount_or_allowance' => $service->price,
                    'subtotal' => 0.00,
                    'billing_type' => 'charged_to_room',
                    'status' => 'delivered', // Instant fulfillment
                    'is_free_inclusion' => true,
                    'notes' => 'Klaim Langsung (Quick Claim) pada ' . Carbon::now()->format('H:i') . ' WIB',
                    'served_by' => auth()->id(),
                    'created_at' => $claimTimestamp,
                    'updated_at' => $claimTimestamp,
                ]);
            });

            return back()->with('success', "Klaim '{$inclusion->category}' untuk Kamar {$booking->room?->room_number} berhasil dicatat (Rp 0).");
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Gagal memproses klaim: ' . $e->getMessage()]);
        }
    }

    /**
     * Store a customized fulfillment order (Kitchen preparation, Laundry wash & fold, Rental equipment).
     */
    public function fulfillmentOrder(Request $request, Booking $booking, RoomOrderService $orderService): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'quantity' => 'required|integer|min:1',
            'detail_notes' => 'nullable|string|max:255',
            'category_type' => 'required|string|in:meal,beverage,laundry,rental,amenity,other',
        ]);

        $service = Service::findOrFail($validated['service_id']);
        $quantity = (int) $validated['quantity'];

        // Calculate allowance using RoomOrderService
        $calc = $orderService->calculateInclusion($booking, $service, $quantity);

        $status = match ($validated['category_type']) {
            'laundry' => 'pending', // Sedang Dicuci
            'rental' => 'pending',  // Sedang Dipinjam
            'meal' => 'pending',    // Sedang Dimasak
            default => 'delivered',
        };

        $prefix = match ($validated['category_type']) {
            'laundry' => '[Laundry Pickup]',
            'rental' => '[Rental Unit]',
            'meal' => '[Pesanan Dapur]',
            default => '[Inklusi Khusus]',
        };

        $note = $prefix . ' ' . ($validated['detail_notes'] ?? 'Order via Fulfillment Desk');

        $claimDate = $request->input('claim_date');
        $claimTimestamp = $claimDate ? Carbon::parse($claimDate)->setTimeFrom(now()) : now();

        try {
            DB::transaction(function () use ($booking, $service, $quantity, $calc, $status, $note, $claimTimestamp) {
                BookingItem::create([
                    'booking_id' => $booking->id,
                    'service_id' => $service->id,
                    'item_name' => $service->name,
                    'quantity' => $quantity,
                    'unit_price' => $calc['unit_price'],
                    'discount_or_allowance' => $calc['discount_or_allowance'],
                    'subtotal' => $calc['subtotal'],
                    'billing_type' => 'charged_to_room',
                    'status' => $status,
                    'is_free_inclusion' => $calc['is_free_inclusion'],
                    'notes' => $note,
                    'served_by' => auth()->id(),
                    'created_at' => $claimTimestamp,
                    'updated_at' => $claimTimestamp,
                ]);
            });

            $coverMsg = $calc['is_free_inclusion']
                ? 'Tercakup Paket Inklusi (Rp 0)'
                : 'Sebagian/Total dikenakan biaya Rp ' . number_format($calc['subtotal'], 0, ',', '.');

            return back()->with('success', "Pesanan '{$service->name}' untuk Kamar {$booking->room?->room_number} berhasil dibuat ({$coverMsg}) dan masuk antrean.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Gagal membuat pesanan layanan: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the progress / status of a fulfillment ticket (e.g. Dapur selesai masak, Laundry selesai diantar).
     */
    public function updateFulfillmentStatus(Request $request, BookingItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,delivered,cancelled',
            'action_note' => 'nullable|string|max:200',
        ]);

        $item->status = $validated['status'];

        if (!empty($validated['action_note'])) {
            $item->notes = ($item->notes ? $item->notes . ' | ' : '') . $validated['action_note'] . ' (' . Carbon::now()->format('H:i') . ')';
        }

        $item->save();

        $statusLabel = match ($validated['status']) {
            'delivered' => 'Telah Diselesaikan / Disajikan',
            'cancelled' => 'Dibatalkan',
            default => 'Sedang Diproses',
        };

        return back()->with('success', "Status layanan '{$item->item_name}' diperbarui menjadi: {$statusLabel}.");
    }
}

