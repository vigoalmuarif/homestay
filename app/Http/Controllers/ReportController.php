<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display the financial and operational report filtered by date range.
     */
    public function index(Request $request): View
    {
        $dates = $this->resolveDateRange($request);
        $startDate = $dates['start_date'];
        $endDate = $dates['end_date'];
        $preset = $dates['preset'];
        $roomTypeId = $request->query('room_type_id');

        $reportData = $this->buildReportData($startDate, $endDate, $roomTypeId ? (int) $roomTypeId : null);
        $roomTypes = RoomType::all();

        return view('pages.reports.index', array_merge($reportData, [
            'title' => 'Laporan Rekapitulasi Finansial & Operasional',
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'preset' => $preset,
            'selectedRoomTypeId' => $roomTypeId,
            'roomTypes' => $roomTypes,
        ]));
    }

    /**
     * Export the filtered report data as CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $dates = $this->resolveDateRange($request);
        $startDate = $dates['start_date'];
        $endDate = $dates['end_date'];
        $roomTypeId = $request->query('room_type_id');

        $reportData = $this->buildReportData($startDate, $endDate, $roomTypeId ? (int) $roomTypeId : null);
        $bookings = $reportData['bookings'];

        $filename = 'laporan-homestay-' . $startDate->format('Ymd') . '-sd-' . $endDate->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($bookings, $reportData, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            // Header Summary
            fputcsv($handle, ['LAPORAN OPERASIONAL & KEUANGAN HOMESTAY']);
            fputcsv($handle, ['Periode', $startDate->format('d/m/Y') . ' s.d. ' . $endDate->format('d/m/Y')]);
            fputcsv($handle, ['Total Omzet Kotor (Rp)', number_format($reportData['metrics']['gross_revenue'], 0, ',', '.')]);
            fputcsv($handle, ['Pendapatan Sewa Kamar (Rp)', number_format($reportData['metrics']['total_room_revenue'], 0, ',', '.')]);
            fputcsv($handle, ['Pendapatan F&B & Layanan (Rp)', number_format($reportData['metrics']['total_service_revenue'], 0, ',', '.')]);
            fputcsv($handle, ['Uang Jaminan / Deposit (Rp)', number_format($reportData['metrics']['total_deposits'], 0, ',', '.')]);
            fputcsv($handle, ['Piutang / Sisa Tagihan (Rp)', number_format($reportData['metrics']['total_balance_due'], 0, ',', '.')]);
            fputcsv($handle, ['Malam Kamar Terjual', $reportData['metrics']['total_nights_sold']]);
            fputcsv($handle, ['Estimasi Okupansi Rata-rata', $reportData['metrics']['occupancy_rate'] . '%']);
            fputcsv($handle, []);

            // Detail Table
            fputcsv($handle, [
                'Kode Booking',
                'Nama Tamu',
                'No Telepon',
                'Kamar',
                'Tipe Kamar',
                'Check-In',
                'Check-Out',
                'Malam',
                'Tarif/Malam (Rp)',
                'Sewa Kamar (Rp)',
                'Layanan & POS (Rp)',
                'Total Tagihan Kotor (Rp)',
                'Deposit (Rp)',
                'Sisa Tagihan (Rp)',
                'Status Bayar',
                'Status Reservasi',
            ]);

            foreach ($bookings as $b) {
                $roomCharge = (float) ($b->room_price_snapshot * max(1, $b->total_nights));
                $posCharge = (float) $b->items->where('status', '!=', 'cancelled')->sum('subtotal');
                $gross = $roomCharge + (float) $b->items->where('billing_type', 'charged_to_room')->where('status', '!=', 'cancelled')->sum('subtotal');
                $deposit = (float) ($b->deposit_amount ?? 0);
                $due = max(0, $gross - $deposit);

                fputcsv($handle, [
                    $b->booking_code,
                    $b->customer?->full_name ?? '-',
                    $b->customer?->phone_number ?? '-',
                    $b->room?->room_number ?? '-',
                    $b->room?->roomType?->name ?? '-',
                    $b->check_in_date->format('d/m/Y H:i'),
                    $b->check_out_date->format('d/m/Y H:i'),
                    $b->total_nights,
                    $b->room_price_snapshot,
                    $roomCharge,
                    $posCharge,
                    $gross,
                    $deposit,
                    $due,
                    ucfirst($b->payment_status),
                    ucfirst($b->status),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Resolve start and end dates from request presets or custom inputs.
     *
     * @return array{start_date: Carbon, end_date: Carbon, preset: string}
     */
    protected function resolveDateRange(Request $request): array
    {
        $preset = $request->query('preset', 'this_month');
        $now = Carbon::now();

        switch ($preset) {
            case 'today':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'last_7_days':
                $startDate = $now->copy()->subDays(6)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'last_30_days':
                $startDate = $now->copy()->subDays(29)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth();
                $endDate = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'this_year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'custom':
                $rawStart = $request->query('start_date');
                $rawEnd = $request->query('end_date');
                $startDate = $rawStart ? Carbon::parse($rawStart)->startOfDay() : $now->copy()->startOfMonth();
                $endDate = $rawEnd ? Carbon::parse($rawEnd)->endOfDay() : $now->copy()->endOfDay();
                if ($endDate->lt($startDate)) {
                    $endDate = $startDate->copy()->endOfDay();
                }
                break;
            case 'this_month':
            default:
                $preset = 'this_month';
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfDay();
                break;
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'preset' => $preset,
        ];
    }

    /**
     * Aggregate report financial & operational data.
     */
    protected function buildReportData(Carbon $startDate, Carbon $endDate, ?int $roomTypeId = null): array
    {
        $bookingsQuery = Booking::with(['customer', 'room.roomType', 'items.service'])
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('check_in_date', [$startDate, $endDate])
                    ->orWhereBetween('check_out_date', [$startDate, $endDate])
                    ->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->where('check_in_date', '<=', $startDate)
                            ->where('check_out_date', '>=', $endDate);
                    });
            })
            ->latest('check_in_date');

        if ($roomTypeId) {
            $bookingsQuery->whereHas('room', fn($q) => $q->where('room_type_id', $roomTypeId));
        }

        $bookings = $bookingsQuery->get();

        $totalRoomRevenue = 0.00;
        $totalServiceRevenue = 0.00;
        $totalPaidInstantly = 0.00;
        $totalDeposits = 0.00;
        $totalBalanceDue = 0.00;
        $totalNightsSold = 0;

        $categoryBreakdown = [
            'meal' => ['label' => 'Makanan & Sarapan', 'total' => 0.00, 'qty' => 0],
            'beverage' => ['label' => 'Minuman & Kopi', 'total' => 0.00, 'qty' => 0],
            'laundry' => ['label' => 'Laundry Pakaian', 'total' => 0.00, 'qty' => 0],
            'rental' => ['label' => 'Sewa Kendaraan/Alat', 'total' => 0.00, 'qty' => 0],
            'amenity' => ['label' => 'Amenitas & Perlengkapan', 'total' => 0.00, 'qty' => 0],
            'other' => ['label' => 'Layanan Lainnya', 'total' => 0.00, 'qty' => 0],
        ];

        // Daily aggregated statistics map: 'Y-m-d' => [room_rev, service_rev, checkins, checkouts]
        $dailySummary = [];
        $cursor = $startDate->copy()->startOfDay();
        $endCursor = $endDate->copy()->startOfDay();

        while ($cursor->lte($endCursor)) {
            $dailySummary[$cursor->format('Y-m-d')] = [
                'date' => $cursor->format('Y-m-d'),
                'day_name' => $cursor->translatedFormat('l, d M Y'),
                'room_revenue' => 0.00,
                'service_revenue' => 0.00,
                'checkins_count' => 0,
                'checkouts_count' => 0,
                'total_daily' => 0.00,
            ];
            $cursor->addDay();
        }

        foreach ($bookings as $b) {
            $nights = max(1, (int) ($b->total_nights ?: 1));
            $roomCharge = (float) ($b->room_price_snapshot * $nights);
            $deposit = (float) ($b->deposit_amount ?? 0);

            $totalRoomRevenue += $roomCharge;
            $totalDeposits += $deposit;
            $totalNightsSold += $nights;

            // Map checkin & checkout dates into daily summary
            $checkInKey = $b->check_in_date->format('Y-m-d');
            $checkOutKey = $b->check_out_date->format('Y-m-d');

            if (isset($dailySummary[$checkInKey])) {
                $dailySummary[$checkInKey]['checkins_count']++;
                $dailySummary[$checkInKey]['room_revenue'] += $roomCharge;
            }
            if (isset($dailySummary[$checkOutKey])) {
                $dailySummary[$checkOutKey]['checkouts_count']++;
            }

            // Items breakdown
            $activeItems = $b->items->filter(fn(BookingItem $i) => $i->status !== 'cancelled');

            foreach ($activeItems as $item) {
                $itemSubtotal = (float) $item->subtotal;
                $category = $item->service?->category ?? 'other';

                if (isset($categoryBreakdown[$category])) {
                    $categoryBreakdown[$category]['total'] += $itemSubtotal;
                    $categoryBreakdown[$category]['qty'] += (int) $item->quantity;
                } else {
                    $categoryBreakdown['other']['total'] += $itemSubtotal;
                    $categoryBreakdown['other']['qty'] += (int) $item->quantity;
                }

                if ($item->billing_type === 'charged_to_room') {
                    $totalServiceRevenue += $itemSubtotal;
                } else {
                    $totalPaidInstantly += $itemSubtotal;
                }

                $orderDateKey = $item->created_at->format('Y-m-d');
                if (isset($dailySummary[$orderDateKey])) {
                    $dailySummary[$orderDateKey]['service_revenue'] += $itemSubtotal;
                }
            }

            $grossBooking = $roomCharge + (float) $activeItems->where('billing_type', 'charged_to_room')->sum('subtotal');
            $balanceDue = max(0.00, $grossBooking - $deposit);
            $totalBalanceDue += $balanceDue;
        }

        // Calculate daily totals
        foreach ($dailySummary as $k => $d) {
            $dailySummary[$k]['total_daily'] = $d['room_revenue'] + $d['service_revenue'];
        }

        $grossRevenue = $totalRoomRevenue + $totalServiceRevenue + $totalPaidInstantly;

        // Okupansi estimation
        $totalRooms = Room::count();
        $daysCount = max(1, $startDate->diffInDays($endDate) + 1);
        $totalRoomCapacity = $totalRooms * $daysCount;
        $occupancyRate = $totalRoomCapacity > 0 ? round(($totalNightsSold / $totalRoomCapacity) * 100, 1) : 0.0;
        $adr = $totalNightsSold > 0 ? round($totalRoomRevenue / $totalNightsSold, 0) : 0;
        $revpar = $totalRoomCapacity > 0 ? round($totalRoomRevenue / $totalRoomCapacity, 0) : 0;

        return [
            'bookings' => $bookings,
            'metrics' => [
                'gross_revenue' => $grossRevenue,
                'total_room_revenue' => $totalRoomRevenue,
                'total_service_revenue' => $totalServiceRevenue,
                'total_paid_instantly' => $totalPaidInstantly,
                'total_deposits' => $totalDeposits,
                'total_balance_due' => $totalBalanceDue,
                'total_bookings_count' => $bookings->count(),
                'total_nights_sold' => $totalNightsSold,
                'occupancy_rate' => $occupancyRate,
                'adr' => $adr,
                'revpar' => $revpar,
                'days_count' => $daysCount,
            ],
            'categoryBreakdown' => $categoryBreakdown,
            'dailySummary' => array_reverse($dailySummary),
        ];
    }
}

