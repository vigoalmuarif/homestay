<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\BookingConflictService;
use App\Services\FolioCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Display listing of in-house guests and bookings.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status', 'all');
        $dateFilter = $request->query('date_filter', 'all');

        $query = Booking::with(['customer', 'room.roomType'])->latest();

        if (filled($search)) {
            $query->search($search);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($dateFilter === 'today_arrivals') {
            $query->whereDate('check_in_date', Carbon::today());
        } elseif ($dateFilter === 'today_departures') {
            $query->whereDate('check_out_date', Carbon::today());
        }

        $bookings = $query->paginate(15)->withQueryString();

        $stats = [
            'in_house' => Booking::inHouse()->count(),
            'arriving_today' => Booking::arrivingToday()->count(),
            'departing_today' => Booking::departingToday()->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'total' => Booking::count(),
        ];

        return view('pages.bookings.index', [
            'title' => 'Reservasi & Front Desk (Tamu Aktif)',
            'bookings' => $bookings,
            'stats' => $stats,
            'search' => $search,
            'status' => $status,
            'dateFilter' => $dateFilter,
        ]);
    }

    /**
     * Show form for creating a new booking.
     */
    public function create(Request $request): View
    {
        $customers = Customer::active()->orderBy('full_name')->get();
        $roomTypes = RoomType::with(['rooms', 'inclusions.service'])->orderBy('name')->get();
        $rooms = Room::with('roomType')->where('status', '!=', 'maintenance')->orderBy('room_number')->get();

        $defaultCheckIn = $request->query('check_in', Carbon::today()->format('Y-m-d'));
        $defaultCheckOut = $request->query('check_out', Carbon::tomorrow()->format('Y-m-d'));
        $selectedRoomId = $request->query('room_id');
        $selectedCustomerId = $request->query('customer_id');
        $selectedCustomer = $selectedCustomerId ? Customer::find($selectedCustomerId) : null;
        $selectedRoomTypeId = $request->query('room_type_id');
        if (! $selectedRoomTypeId && $selectedRoomId) {
            $selectedRoomTypeId = Room::find($selectedRoomId)?->room_type_id;
        }

        return view('pages.bookings.create', [
            'title' => 'Buat Reservasi Baru',
            'customers' => $customers,
            'roomTypes' => $roomTypes,
            'rooms' => $rooms,
            'defaultCheckIn' => $defaultCheckIn,
            'defaultCheckOut' => $defaultCheckOut,
            'selectedRoomId' => $selectedRoomId,
            'selectedCustomerId' => $selectedCustomerId,
            'selectedCustomer' => $selectedCustomer,
            'selectedRoomTypeId' => $selectedRoomTypeId,
        ]);
    }

    /**
     * Store a newly created booking in storage.
     */
    public function store(StoreBookingRequest $request, BookingConflictService $conflictService): RedirectResponse
    {
        $validated = $request->validated();

        $totalNights = $conflictService->calculateNights($validated['check_in_date'], $validated['check_out_date']);

        $bookingData = array_merge($validated, [
            'booking_code' => Booking::generateCode(),
            'total_nights' => $totalNights,
        ]);

        if (($bookingData['status'] ?? 'confirmed') === 'checked_in') {
            $bookingData['actual_check_in_at'] = now();
        }

        $booking = Booking::create($bookingData);

        if ($booking->status === 'checked_in') {
            $booking->room->update(['status' => 'occupied']);
        }

        return redirect()->route('bookings.show', $booking)->with('success', "Reservasi {$booking->booking_code} berhasil dibuat.");
    }

    /**
     * Display the specified booking detail & folio preview.
     */
    public function show(Booking $booking): View
    {
        $booking->load([
            'customer',
            'room.roomType.inclusions.service',
            'items' => fn($q) => $q->latest(),
            'items.service',
            'items.servedBy',
        ]);

        $services = \App\Models\Service::active()->orderBy('category')->orderBy('name')->get();

        return view('pages.bookings.show', [
            'title' => "Detail Reservasi {$booking->booking_code}",
            'booking' => $booking,
            'services' => $services,
        ]);
    }

    /**
     * Show form for editing an existing booking.
     */
    public function edit(Booking $booking): View
    {
        $booking->load(['customer', 'room.roomType']);
        $customers = Customer::active()->orderBy('full_name')->get();
        $roomTypes = RoomType::with('rooms')->orderBy('name')->get();
        $rooms = Room::with('roomType')->where('status', '!=', 'maintenance')->orderBy('room_number')->get();

        return view('pages.bookings.edit', [
            'title' => "Edit Reservasi {$booking->booking_code}",
            'booking' => $booking,
            'customers' => $customers,
            'roomTypes' => $roomTypes,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Update the specified booking in storage.
     */
    public function update(UpdateBookingRequest $request, Booking $booking, BookingConflictService $conflictService): RedirectResponse
    {
        $validated = $request->validated();

        $totalNights = $conflictService->calculateNights($validated['check_in_date'], $validated['check_out_date']);
        $validated['total_nights'] = $totalNights;

        $oldStatus = $booking->status;
        $booking->update($validated);

        if ($booking->status === 'checked_in' && $oldStatus !== 'checked_in') {
            $booking->actual_check_in_at = now();
            $booking->save();
            $booking->room->update(['status' => 'occupied']);
        } elseif ($booking->status === 'checked_out' && $oldStatus !== 'checked_out') {
            $booking->actual_check_out_at = now();
            $booking->save();
            $booking->room->update(['status' => 'dirty']);
        }

        return redirect()->route('bookings.show', $booking)->with('success', "Reservasi {$booking->booking_code} berhasil diperbarui.");
    }

    /**
     * Remove the specified booking from storage.
     */
    public function destroy(Booking $booking): RedirectResponse
    {
        if ($booking->status === 'checked_in') {
            return back()->withErrors(['error' => 'Reservasi yang sedang menginap (In-House) tidak dapat langsung dihapus. Lakukan Check-Out atau pembatalan terlebih dahulu.']);
        }

        $code = $booking->booking_code;
        $booking->delete();

        return redirect()->route('bookings.index')->with('success', "Data reservasi {$code} berhasil dihapus.");
    }

    /**
     * Quick action: Check-in guest.
     */
    public function checkIn(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->status === 'checked_in') {
            return back()->with('info', 'Tamu sudah dalam status Check-In.');
        }

        if ($booking->status === 'cancelled') {
            return back()->withErrors(['error' => 'Reservasi yang sudah dibatalkan tidak dapat langsung di-check-in.']);
        }

        $deposit = $request->input('deposit_amount');
        if (is_numeric($deposit) && (float)$deposit >= 0) {
            $booking->deposit_amount = (float)$deposit;
        }

        $booking->status = 'checked_in';
        $booking->actual_check_in_at = now();
        $booking->save();

        $booking->room->update(['status' => 'occupied']);

        return back()->with('success', "Tamu {$booking->customer->full_name} berhasil Check-In di Kamar {$booking->room->room_number}.");
    }

    /**
     * Get folio summary JSON for checkout modal calculation.
     */
    public function folioSummary(Booking $booking, FolioCalculationService $folioService): JsonResponse
    {
        $folio = $folioService->calculateFolio($booking);

        return response()->json([
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'customer_name' => $booking->customer?->full_name ?? 'Tamu',
            'room_number' => $booking->room?->room_number ?? '-',
            'room_type' => $booking->room?->roomType?->name ?? '-',
            'total_nights' => $folio['total_nights'],
            'room_price_snapshot' => (float) $folio['room_price_snapshot'],
            'total_room_charge' => (float) $folio['total_room_charge'],
            'total_charged_to_room' => (float) $folio['total_charged_to_room'],
            'gross_total' => (float) $folio['gross_total'],
            'deposit_amount' => (float) $folio['deposit_amount'],
            'balance_due' => (float) $folio['balance_due'],
            'refund_due' => (float) $folio['refund_due'],
            'payment_status' => $booking->payment_status,
            'is_settled' => (bool) $folio['is_settled'],
            'unpaid_items' => $folio['charged_to_room_items']->map(fn($item) => [
                'name' => $item->item_name,
                'quantity' => $item->quantity,
                'subtotal' => (float) $item->subtotal,
            ])->values(),
        ]);
    }

    /**
     * Quick action: Check-out guest with folio validation and settlement handling.
     */
    public function checkOut(Request $request, Booking $booking, FolioCalculationService $folioService): RedirectResponse
    {
        if ($booking->status !== 'checked_in') {
            return back()->withErrors(['error' => 'Hanya tamu dengan status Check-In yang dapat melakukan Check-Out.']);
        }

        $folio = $folioService->calculateFolio($booking);
        $balanceDue = (float) $folio['balance_due'];
        $refundDue = (float) $folio['refund_due'];

        // Skenario 2: Masih ada sisa tagihan yang belum dibayar
        if ($balanceDue > 0.00) {
            // Opsi A: Selesaikan pelunasan kasir langsung (Quick Settlement)
            if ($request->boolean('settle_payment') || $request->filled('amount_paid')) {
                $validated = $request->validate([
                    'amount_paid' => ['required', 'numeric', 'min:0'],
                    'payment_method' => ['required', 'string', 'in:cash,qris,transfer,card,deposit_offset'],
                    'payment_notes' => ['nullable', 'string', 'max:500'],
                ]);

                $folioService->settle($booking, [
                    'amount_paid' => $validated['amount_paid'],
                    'payment_method' => $validated['payment_method'],
                    'notes' => $validated['payment_notes'] ?? null,
                    'auto_checkout' => true,
                ]);

                return back()->with('success', "Pelunasan tagihan berhasil & Tamu Kamar {$booking->room?->room_number} telah selesai Check-Out. Kamar berstatus Dirty.");
            }

            // Opsi B: Check-Out bersyarat sebagai piutang (City Ledger / Corporate / Tamu Khusus)
            if ($request->boolean('allow_unsettled')) {
                $validated = $request->validate([
                    'unsettled_reason' => ['required', 'string', 'min:3', 'max:500'],
                ]);

                $timestamp = Carbon::now()->format('d/m/Y H:i');
                $log = "[Check-Out Piutang {$timestamp}] Sisa Tagihan: Rp " . number_format($balanceDue, 0, ',', '.') . " | Alasan/Penjamin: {$validated['unsettled_reason']}";
                $booking->notes = trim(($booking->notes ? $booking->notes . "\n" : '') . $log);

                $booking->status = 'checked_out';
                $booking->actual_check_out_at = Carbon::now();
                $booking->save();

                if ($booking->room) {
                    $booking->room->update(['status' => 'dirty']);
                }

                return back()->with('warning', "Check-Out bersyarat berhasil dicatat sebagai piutang (Sisa: Rp " . number_format($balanceDue, 0, ',', '.') . "). Kamar {$booking->room?->room_number} kini berstatus Dirty.");
            }

            // Blokir check-out jika tidak ada settlement atau izin piutang
            return back()->withErrors([
                'error' => "Tamu masih memiliki sisa tagihan sebesar Rp " . number_format($balanceDue, 0, ',', '.') . ". Harap selesaikan pembayaran tagihan atau gunakan opsi Check-Out Piutang terlebih dahulu.",
            ]);
        }

        // Skenario 3: Ada deposit berlebih yang perlu dikembalikan ke tamu
        if ($refundDue > 0.00 && $request->boolean('refund_confirmed')) {
            $timestamp = Carbon::now()->format('d/m/Y H:i');
            $refundNotes = $request->input('refund_notes');
            $log = "[Pengembalian Deposit {$timestamp}] Nominal: Rp " . number_format($refundDue, 0, ',', '.') . " telah diserahkan kembali kepada tamu.";
            if (filled($refundNotes)) {
                $log .= " | Catatan: {$refundNotes}";
            }
            $booking->notes = trim(($booking->notes ? $booking->notes . "\n" : '') . $log);
        }

        // Skenario 1: Tagihan lunas bersih
        $booking->status = 'checked_out';
        $booking->actual_check_out_at = Carbon::now();
        $booking->save();

        if ($booking->room) {
            $booking->room->update(['status' => 'dirty']);
        }

        return back()->with('success', "Check-Out berhasil. Kamar {$booking->room->room_number} kini berstatus Dirty (Perlu dibersihkan).");
    }

    /**
     * Quick action: Cancel reservation.
     */
    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $booking->status = 'cancelled';
        $booking->save();

        // If room is marked occupied by this booking, restore to available
        if ($booking->room->status === 'occupied') {
            $booking->room->update(['status' => 'available']);
        }

        return back()->with('success', "Reservasi {$booking->booking_code} telah dibatalkan.");
    }

    /**
     * API: Get available rooms for given date range.
     */
    public function availableRooms(Request $request, BookingConflictService $conflictService): JsonResponse
    {
        $checkIn = $request->query('check_in');
        $checkOut = $request->query('check_out');
        $roomTypeId = $request->query('room_type_id');
        $excludeBookingId = $request->query('exclude_booking_id');

        if (!$checkIn || !$checkOut || $checkOut <= $checkIn) {
            return response()->json([], 422);
        }

        $rooms = $conflictService->getAvailableRooms(
            $checkIn,
            $checkOut,
            $roomTypeId ? (int)$roomTypeId : null,
            $excludeBookingId ? (int)$excludeBookingId : null
        );

        $data = $rooms->map(fn($r) => [
            'id' => $r->id,
            'room_number' => $r->room_number,
            'room_type_id' => $r->room_type_id,
            'room_type_name' => $r->roomType?->name,
            'base_price' => $r->roomType?->base_price,
            'floor' => $r->floor,
        ]);

        return response()->json($data);
    }

    /**
     * API: Calendar events for occupancy visualization.
     */
    public function calendarEvents(Request $request): JsonResponse
    {
        $start = $request->query('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end = $request->query('end', Carbon::now()->endOfMonth()->addMonth()->format('Y-m-d'));

        $bookings = Booking::with(['customer', 'room.roomType'])
            ->whereNotIn('status', ['cancelled'])
            ->where('check_in_date', '<=', $end)
            ->where('check_out_date', '>=', $start)
            ->get();

        $events = $bookings->map(function ($b) {
            $calendarLevel = match ($b->status) {
                'checked_in' => 'Info',
                'confirmed' => 'Primary',
                'checked_out' => 'Success',
                default => 'Warning',
            };

            return [
                'id' => $b->id,
                'title' => "Rm {$b->room->room_number} - {$b->customer->full_name}",
                'start' => $b->check_in_date->format('Y-m-d'),
                'end' => $b->check_out_date->format('Y-m-d'),
                'url' => route('bookings.show', $b),
                'extendedProps' => [
                    'calendar' => $calendarLevel,
                    'code' => $b->booking_code,
                    'customer' => $b->customer->full_name,
                    'room' => $b->room->room_number,
                    'status' => $b->status,
                ],
            ];
        });

        return response()->json($events);
    }
}
