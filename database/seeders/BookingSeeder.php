<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::where('is_blacklisted', false)->get();
        $rooms = Room::with('roomType')->get();

        if ($customers->count() < 3 || $rooms->count() < 3) {
            return;
        }

        $today = Carbon::today();

        // 1. In-House guest (checked in today, check out in 2 days)
        $room1 = $rooms->where('room_number', '101')->first() ?? $rooms[0];
        $booking1 = Booking::create([
            'booking_code' => 'BK-' . $today->format('Ymd') . '-A101',
            'customer_id' => $customers[0]->id,
            'room_id' => $room1->id,
            'check_in_date' => $today->copy()->setTime(14, 0)->format('Y-m-d H:i:s'),
            'check_out_date' => $today->copy()->addDays(2)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'total_nights' => 2,
            'room_price_snapshot' => $room1->roomType?->base_price ?? 350000.00,
            'deposit_amount' => 100000.00,
            'status' => 'checked_in',
            'payment_status' => 'partial',
            'notes' => 'Tamu meminta ekstra selimut dan sarapan jam 07:00.',
            'actual_check_in_at' => $today->copy()->setTime(14, 15),
        ]);
        $room1->update(['status' => 'occupied']);

        // 2. Arriving today (confirmed)
        $room2 = $rooms->where('room_number', '102')->first() ?? $rooms[1];
        Booking::create([
            'booking_code' => 'BK-' . $today->format('Ymd') . '-B102',
            'customer_id' => $customers[1]->id,
            'room_id' => $room2->id,
            'check_in_date' => $today->copy()->setTime(14, 0)->format('Y-m-d H:i:s'),
            'check_out_date' => $today->copy()->addDays(3)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'total_nights' => 3,
            'room_price_snapshot' => $room2->roomType?->base_price ?? 350000.00,
            'deposit_amount' => 200000.00,
            'status' => 'confirmed',
            'payment_status' => 'partial',
            'notes' => 'Rencana tiba sekitar pukul 15:00 WIB.',
        ]);

        // 3. Checked out earlier today
        $room3 = $rooms->where('room_number', '201')->first() ?? $rooms[2];
        Booking::create([
            'booking_code' => 'BK-' . $today->copy()->subDays(2)->format('Ymd') . '-C201',
            'customer_id' => $customers[2]->id,
            'room_id' => $room3->id,
            'check_in_date' => $today->copy()->subDays(2)->setTime(14, 0)->format('Y-m-d H:i:s'),
            'check_out_date' => $today->copy()->setTime(12, 0)->format('Y-m-d H:i:s'),
            'total_nights' => 2,
            'room_price_snapshot' => $room3->roomType?->base_price ?? 500000.00,
            'deposit_amount' => 0.00,
            'status' => 'checked_out',
            'payment_status' => 'paid',
            'notes' => 'Check-out selesai pukul 11:30. Kunci telah dikembalikan.',
            'actual_check_in_at' => $today->copy()->subDays(2)->setTime(13, 45),
            'actual_check_out_at' => $today->copy()->setTime(11, 30),
        ]);
        $room3->update(['status' => 'dirty']);

        // 4. Future booking next week
        if (isset($customers[3]) && isset($rooms[3])) {
            Booking::create([
                'booking_code' => 'BK-' . $today->copy()->addDays(7)->format('Ymd') . '-D301',
                'customer_id' => $customers[3]->id,
                'room_id' => $rooms[3]->id,
                'check_in_date' => $today->copy()->addDays(7)->setTime(14, 0)->format('Y-m-d H:i:s'),
                'check_out_date' => $today->copy()->addDays(9)->setTime(12, 0)->format('Y-m-d H:i:s'),
                'total_nights' => 2,
                'room_price_snapshot' => $rooms[3]->roomType?->base_price ?? 850000.00,
                'deposit_amount' => 300000.00,
                'status' => 'confirmed',
                'payment_status' => 'partial',
                'notes' => 'Family vacation.',
            ]);
        }
    }
}

