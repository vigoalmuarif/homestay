<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\FolioCalculationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BillingController extends Controller
{
    /**
     * Display a listing of folios with metrics and filters.
     */
    public function index(Request $request, FolioCalculationService $folioService): View
    {
        $search = $request->query('search');
        $paymentStatus = $request->query('payment_status', 'all');
        $status = $request->query('status', 'all');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Booking::with(['customer', 'room.roomType', 'items'])
            ->latest('id');

        if (filled($search)) {
            $query->search($search);
        }

        if ($paymentStatus !== 'all' && in_array($paymentStatus, ['unpaid', 'partial', 'paid'])) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($status !== 'all' && in_array($status, ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'])) {
            $query->where('status', $status);
        }

        if (filled($startDate)) {
            $query->whereDate('check_in_date', '>=', $startDate);
        }

        if (filled($endDate)) {
            $query->whereDate('check_out_date', '<=', $endDate);
        }

        $bookings = $query->paginate(10)->withQueryString();

        $metrics = $folioService->getMetrics($startDate, $endDate);

        return view('pages.billing.index', [
            'title' => 'Folio Billing & Laporan Keuangan',
            'bookings' => $bookings,
            'metrics' => $metrics,
            'search' => $search,
            'paymentStatus' => $paymentStatus,
            'status' => $status,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Display single reservation folio statement and settlement portal.
     */
    public function show(Booking $booking, FolioCalculationService $folioService): View
    {
        $folio = $folioService->calculateFolio($booking);

        return view('pages.billing.show', [
            'title' => "Folio Settlement - {$booking->booking_code}",
            'booking' => $booking,
            'folio' => $folio,
        ]);
    }

    /**
     * Process checkout settlement payment for a booking folio.
     */
    public function settle(Request $request, Booking $booking, FolioCalculationService $folioService): RedirectResponse
    {
        $validated = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'in:cash,qris,transfer,card,deposit_offset'],
            'notes' => ['nullable', 'string', 'max:500'],
            'auto_checkout' => ['nullable', 'boolean'],
        ]);

        $folioService->settle($booking, $validated);

        $actionMsg = !empty($validated['auto_checkout']) && $booking->status === 'checked_out'
            ? "Pelunasan tagihan berhasil & tamu telah selesai Check-Out (Kamar {$booking->room->room_number} berstatus Dirty)."
            : "Pembayaran tagihan folio {$booking->booking_code} berhasil dicatat.";

        return back()->with('success', $actionMsg);
    }

    /**
     * Printable Guest Folio Invoice.
     */
    public function invoice(Booking $booking, FolioCalculationService $folioService): View
    {
        $folio = $folioService->calculateFolio($booking);

        return view('pages.billing.invoice', [
            'title' => "Invoice #{$booking->booking_code}",
            'booking' => $booking,
            'folio' => $folio,
        ]);
    }

    /**
     * Stream CSV export of bookings & financial records.
     */
    public function exportCsv(Request $request, FolioCalculationService $folioService): StreamedResponse
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $paymentStatus = $request->query('payment_status', 'all');

        $query = Booking::with(['customer', 'room.roomType', 'items'])
            ->whereNotIn('status', ['cancelled'])
            ->latest('id');

        if (filled($startDate)) {
            $query->whereDate('check_in_date', '>=', $startDate);
        }
        if (filled($endDate)) {
            $query->whereDate('check_out_date', '<=', $endDate);
        }
        if ($paymentStatus !== 'all' && in_array($paymentStatus, ['unpaid', 'partial', 'paid'])) {
            $query->where('payment_status', $paymentStatus);
        }

        $bookings = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="laporan-folio-' . date('Ymd-His') . '.csv"',
        ];

        return response()->stream(function () use ($bookings, $folioService) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Kode Booking',
                'Nama Tamu',
                'No Telepon',
                'Nomor Kamar',
                'Tipe Kamar',
                'Check-In',
                'Check-Out',
                'Jumlah Malam',
                'Tarif Kamar/Malam (Rp)',
                'Total Sewa Kamar (Rp)',
                'Layanan & Mini-POS (Rp)',
                'Total Tagihan Kotor (Rp)',
                'Uang Jaminan / Deposit (Rp)',
                'Sisa Tagihan Pelunasan (Rp)',
                'Status Pembayaran',
                'Status Reservasi',
                'Waktu Dibuat',
            ]);

            foreach ($bookings as $b) {
                $folio = $folioService->calculateFolio($b);

                fputcsv($handle, [
                    $b->booking_code,
                    $b->customer?->full_name ?? '-',
                    $b->customer?->phone_number ?? '-',
                    $b->room?->room_number ?? '-',
                    $b->room?->roomType?->name ?? '-',
                    $b->check_in_date->format('Y-m-d H:i'),
                    $b->check_out_date->format('Y-m-d H:i'),
                    $folio['total_nights'],
                    $folio['room_price_snapshot'],
                    $folio['total_room_charge'],
                    $folio['total_charged_to_room'],
                    $folio['gross_total'],
                    $folio['deposit_amount'],
                    $folio['balance_due'],
                    ucfirst($b->payment_status),
                    ucfirst($b->status),
                    $b->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}

