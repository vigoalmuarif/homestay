<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FolioCalculationService
{
    /**
     * Calculate comprehensive folio statement for a booking.
     *
     * @return array{
     *     booking: Booking,
     *     total_nights: int,
     *     room_price_snapshot: float,
     *     total_room_charge: float,
     *     all_items: Collection<int, BookingItem>,
     *     charged_to_room_items: Collection<int, BookingItem>,
     *     paid_instantly_items: Collection<int, BookingItem>,
     *     free_inclusion_items: Collection<int, BookingItem>,
     *     total_charged_to_room: float,
     *     total_paid_instantly: float,
     *     total_discounts_or_allowances: float,
     *     gross_total: float,
     *     deposit_amount: float,
     *     balance_due: float,
     *     refund_due: float,
     *     payment_status: string,
     *     is_settled: bool
     * }
     */
    public function calculateFolio(Booking $booking): array
    {
        // Ensure relationships are loaded
        if (!$booking->relationLoaded('items')) {
            $booking->load(['items.service', 'items.servedBy']);
        }
        if (!$booking->relationLoaded('room.roomType')) {
            $booking->load('room.roomType');
        }
        if (!$booking->relationLoaded('customer')) {
            $booking->load('customer');
        }

        $nights = max(1, (int) ($booking->total_nights ?: 1));
        $roomRate = (float) $booking->room_price_snapshot;
        $totalRoomCharge = (float) ($roomRate * $nights);

        /** @var Collection<int, BookingItem> $items */
        $items = $booking->items;

        $activeItems = $items->filter(fn(BookingItem $item) => $item->status !== 'cancelled');

        $chargedToRoomItems = $activeItems->filter(fn(BookingItem $item) => $item->billing_type === 'charged_to_room');
        $paidInstantlyItems = $activeItems->filter(fn(BookingItem $item) => $item->billing_type === 'paid_instantly');
        $freeInclusionItems = $activeItems->filter(fn(BookingItem $item) => (bool) $item->is_free_inclusion);

        $totalChargedToRoom = (float) $chargedToRoomItems->sum('subtotal');
        $totalPaidInstantly = (float) $paidInstantlyItems->sum('subtotal');
        $totalDiscountsOrAllowances = (float) $activeItems->sum('discount_or_allowance');

        $grossTotal = (float) ($totalRoomCharge + $totalChargedToRoom);
        $depositPaid = (float) ($booking->deposit_amount ?? 0.00);

        $balanceDue = max(0.00, (float) ($grossTotal - $depositPaid));
        $refundDue = max(0.00, (float) ($depositPaid - $grossTotal));

        // Automatically determine calculated payment status
        if ($balanceDue <= 0.00 && ($depositPaid > 0 || $grossTotal == 0)) {
            $paymentStatus = 'paid';
        } elseif ($depositPaid > 0 && $balanceDue > 0) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'unpaid';
        }

        // If database payment_status is out of sync, update it
        if ($booking->payment_status !== $paymentStatus && $booking->exists) {
            $booking->payment_status = $paymentStatus;
            $booking->saveQuietly();
        }

        $isSettled = ($paymentStatus === 'paid' && $balanceDue <= 0.00)
            || ($booking->status === 'checked_out' && $paymentStatus === 'paid');

        return [
            'booking' => $booking,
            'total_nights' => $nights,
            'room_price_snapshot' => $roomRate,
            'total_room_charge' => $totalRoomCharge,
            'all_items' => $items,
            'charged_to_room_items' => $chargedToRoomItems->values(),
            'paid_instantly_items' => $paidInstantlyItems->values(),
            'free_inclusion_items' => $freeInclusionItems->values(),
            'total_charged_to_room' => $totalChargedToRoom,
            'total_paid_instantly' => $totalPaidInstantly,
            'total_discounts_or_allowances' => $totalDiscountsOrAllowances,
            'gross_total' => $grossTotal,
            'deposit_amount' => $depositPaid,
            'balance_due' => $balanceDue,
            'refund_due' => $refundDue,
            'payment_status' => $paymentStatus,
            'is_settled' => $isSettled,
        ];
    }

    /**
     * Process checkout settlement and record final folio payment.
     *
     * @param  array{
     *     amount_paid: float|int|string,
     *     payment_method: string,
     *     notes?: string|null,
     *     auto_checkout?: bool|null
     * }  $data
     */
    public function settle(Booking $booking, array $data): Booking
    {
        $amountPaid = isset($data['amount_paid']) ? (float) $data['amount_paid'] : 0.00;
        if ($amountPaid < 0) {
            throw new InvalidArgumentException('Nominal pembayaran tidak boleh bernilai negatif.');
        }

        $paymentMethod = $data['payment_method'] ?? 'cash';
        $notes = $data['notes'] ?? null;
        $autoCheckout = !empty($data['auto_checkout']);

        return DB::transaction(function () use ($booking, $amountPaid, $paymentMethod, $notes, $autoCheckout) {
            $folio = $this->calculateFolio($booking);
            $balanceDue = $folio['balance_due'];

            // Update deposited/paid amount if payment method is not offsetting existing deposit
            if ($paymentMethod !== 'deposit_offset' && $amountPaid > 0) {
                $booking->deposit_amount = (float) $booking->deposit_amount + $amountPaid;
            }

            // Determine updated payment status
            $currentDeposit = (float) $booking->deposit_amount;
            $newBalanceDue = max(0.00, (float) ($folio['gross_total'] - $currentDeposit));

            if ($paymentMethod === 'deposit_offset') {
                if ($amountPaid >= $balanceDue && $balanceDue > 0) {
                    $booking->payment_status = 'paid';
                } elseif ($balanceDue <= 0) {
                    $booking->payment_status = 'paid';
                } elseif ($amountPaid > 0) {
                    $booking->payment_status = 'partial';
                }
            } else {
                if ($newBalanceDue <= 0.00) {
                    $booking->payment_status = 'paid';
                } elseif ($currentDeposit > 0) {
                    $booking->payment_status = 'partial';
                }
            }

            // Append settlement log into notes
            $timestamp = Carbon::now()->format('d/m/Y H:i');
            $methodLabel = match ($paymentMethod) {
                'cash' => 'Tunai',
                'qris' => 'QRIS',
                'transfer' => 'Transfer Bank',
                'card' => 'Kartu Debit/Kredit',
                'deposit_offset' => 'Potong Uang Jaminan / Deposit',
                default => strtoupper($paymentMethod),
            };

            $log = "[Settlement {$timestamp}] Metode: {$methodLabel}, Nominal: Rp " . number_format($amountPaid, 0, ',', '.');
            if (filled($notes)) {
                $log .= " | Catatan: {$notes}";
            }

            $booking->notes = trim(($booking->notes ? $booking->notes . "\n" : '') . $log);

            // If auto-checkout requested and status is checked_in, complete checkout & dirty the room
            if ($autoCheckout && $booking->status === 'checked_in') {
                $booking->status = 'checked_out';
                $booking->actual_check_out_at = Carbon::now();
                if ($booking->room) {
                    $booking->room->update(['status' => 'dirty']);
                }
            }

            $booking->save();

            return $booking;
        });
    }

    /**
     * Retrieve aggregate financial metrics across bookings.
     */
    public function getMetrics(?string $startDate = null, ?string $endDate = null): array
    {
        $query = Booking::with('items')
            ->whereNotIn('status', ['cancelled']);

        if (filled($startDate)) {
            $query->whereDate('check_in_date', '>=', $startDate);
        }
        if (filled($endDate)) {
            $query->whereDate('check_out_date', '<=', $endDate);
        }

        $bookings = $query->get();

        $totalRoomRevenue = 0.00;
        $totalServiceRevenue = 0.00;
        $totalPaidInstantly = 0.00;
        $totalDeposits = 0.00;
        $totalOutstandingBalance = 0.00;
        $paidCount = 0;
        $partialCount = 0;
        $unpaidCount = 0;

        foreach ($bookings as $b) {
            $nights = max(1, (int) ($b->total_nights ?: 1));
            $roomCharge = (float) ($b->room_price_snapshot * $nights);
            $totalRoomRevenue += $roomCharge;

            $items = $b->items->filter(fn(BookingItem $i) => $i->status !== 'cancelled');
            $chargedItems = $items->filter(fn(BookingItem $i) => $i->billing_type === 'charged_to_room');
            $instantlyItems = $items->filter(fn(BookingItem $i) => $i->billing_type === 'paid_instantly');

            $chargedSubtotal = (float) $chargedItems->sum('subtotal');
            $instantSubtotal = (float) $instantlyItems->sum('subtotal');

            $totalServiceRevenue += $chargedSubtotal;
            $totalPaidInstantly += $instantSubtotal;
            $totalDeposits += (float) ($b->deposit_amount ?? 0);

            $gross = $roomCharge + $chargedSubtotal;
            $deposit = (float) ($b->deposit_amount ?? 0);
            $due = max(0.00, $gross - $deposit);

            $effStatus = $b->payment_status;
            if ($due <= 0.00 && ($deposit > 0 || $gross == 0)) {
                $effStatus = 'paid';
            } elseif ($deposit > 0 && $due > 0) {
                $effStatus = 'partial';
            }

            if ($effStatus === 'paid') {
                $paidCount++;
            } elseif ($effStatus === 'partial') {
                $partialCount++;
                $totalOutstandingBalance += $due;
            } else {
                $unpaidCount++;
                $totalOutstandingBalance += $due;
            }
        }

        $grossRevenue = $totalRoomRevenue + $totalServiceRevenue + $totalPaidInstantly;

        return [
            'total_bookings' => $bookings->count(),
            'gross_revenue' => $grossRevenue,
            'total_room_revenue' => $totalRoomRevenue,
            'total_service_revenue' => $totalServiceRevenue,
            'total_paid_instantly' => $totalPaidInstantly,
            'total_deposits' => $totalDeposits,
            'total_outstanding_balance' => $totalOutstandingBalance,
            'paid_count' => $paidCount,
            'partial_count' => $partialCount,
            'unpaid_count' => $unpaidCount,
        ];
    }
}

