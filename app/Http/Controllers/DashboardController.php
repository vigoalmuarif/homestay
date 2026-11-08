<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the operational homestay dashboard.
     */
    public function index(): View
    {
        $today = Carbon::today();

        $totalRooms = Room::count();
        $occupiedRoomsCount = Room::where('status', 'occupied')->count();
        $availableRoomsCount = Room::where('status', 'available')->count();
        $dirtyRoomsCount = Room::whereIn('status', ['dirty', 'cleaning'])->count();
        $maintenanceRoomsCount = Room::where('status', 'maintenance')->count();

        $occupancyRate = $totalRooms > 0 ? round(($occupiedRoomsCount / $totalRooms) * 100, 1) : 0;

        $todayCheckinsCount = Booking::whereDate('check_in_date', $today)->count();
        $todayDeparturesCount = Booking::whereDate('check_out_date', $today)->count();

        $dailyRevenue = (float) Booking::whereDate('created_at', $today)->sum('deposit_amount');
        if ($dailyRevenue <= 0) {
            $dailyRevenue = (float) Booking::inHouse()->get()->sum('total_room_charge');
        }

        $metrics = [
            'occupied_rooms' => [
                'title' => 'Kamar Terisi (Okupansi)',
                'value' => "{$occupiedRoomsCount} / {$totalRooms} Kamar",
                'change' => "{$occupancyRate}% Terisi",
                'direction' => 'up',
                'status_type' => 'occupied',
            ],
            'today_checkins' => [
                'title' => 'Tamu Check-In Hari Ini',
                'value' => "{$todayCheckinsCount} Tamu",
                'change' => 'Jadwal Hari Ini',
                'direction' => 'up',
                'status_type' => 'available',
            ],
            'dirty_rooms' => [
                'title' => 'Kamar Kotor (Housekeeping)',
                'value' => "{$dirtyRoomsCount} Kamar",
                'change' => 'Perlu Dibersihkan',
                'direction' => 'down',
                'status_type' => 'dirty',
            ],
            'daily_revenue' => [
                'title' => 'Pendapatan Hari Ini',
                'value' => $dailyRevenue,
                'change' => 'Deposit & Kamar Aktif',
                'direction' => 'up',
                'status_type' => 'revenue',
            ],
        ];

        $todayArrivals = Booking::with(['customer', 'room.roomType'])
            ->whereDate('check_in_date', $today)
            ->get()
            ->map(function ($b) {
                return [
                    'booking_code' => $b->booking_code,
                    'guest_name' => $b->customer->full_name,
                    'phone' => $b->customer->phone_number,
                    'room_number' => "Kamar {$b->room->room_number}",
                    'room_type' => $b->room->roomType?->name ?? 'Standard',
                    'scheduled_time' => '14:00 WIB',
                    'status' => $b->status,
                    'status_label' => $b->status_badge['label'],
                    'payment_status' => $b->payment_status,
                    'payment_label' => ucfirst($b->payment_status),
                    'total_amount' => $b->total_room_charge,
                ];
            })
            ->all();

        $todayDepartures = Booking::with(['customer', 'room.roomType'])
            ->whereDate('check_out_date', $today)
            ->get()
            ->map(function ($b) {
                return [
                    'booking_code' => $b->booking_code,
                    'guest_name' => $b->customer->full_name,
                    'phone' => $b->customer->phone_number,
                    'room_number' => "Kamar {$b->room->room_number}",
                    'room_type' => $b->room->roomType?->name ?? 'Standard',
                    'scheduled_time' => '12:00 WIB',
                    'status' => $b->status,
                    'status_label' => $b->status_badge['label'],
                    'room_status' => $b->room->status,
                    'total_amount' => $b->total_room_charge,
                ];
            })
            ->all();

        $roomStatuses = [
            ['name' => 'Available (Siap Huni)', 'count' => $availableRoomsCount, 'badge_class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400', 'dot' => 'bg-emerald-600'],
            ['name' => 'Occupied (Terisi)', 'count' => $occupiedRoomsCount, 'badge_class' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400', 'dot' => 'bg-blue-600'],
            ['name' => 'Dirty (Perlu Pembersihan)', 'count' => $dirtyRoomsCount, 'badge_class' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400', 'dot' => 'bg-amber-500'],
            ['name' => 'Maintenance (Perbaikan)', 'count' => $maintenanceRoomsCount, 'badge_class' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400', 'dot' => 'bg-rose-600'],
        ];

        return view('pages.dashboard.index', [
            'title' => 'Dashboard Operasional Homestay',
            'metrics' => $metrics,
            'todayArrivals' => $todayArrivals,
            'todayDepartures' => $todayDepartures,
            'roomStatuses' => $roomStatuses,
        ]);
    }
}
