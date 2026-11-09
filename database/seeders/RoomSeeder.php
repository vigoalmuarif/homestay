<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Room Types
        $standard = RoomType::firstOrCreate(
            ['name' => 'Standard Double'],
            [
                'base_price' => 350000.00,
                'capacity' => 2,
                'description' => 'Kamar nyaman dengan 1 Queen Bed, AC, Smart TV, dan kamar mandi dalam.',
            ]
        );

        $deluxe = RoomType::firstOrCreate(
            ['name' => 'Deluxe Balcony'],
            [
                'base_price' => 500000.00,
                'capacity' => 2,
                'description' => 'Kamar lantai 2 dengan balkon pribadi pemandangan taman, King Bed, mini-kulkas, dan water heater.',
            ]
        );

        $vip = RoomType::firstOrCreate(
            ['name' => 'VIP Family Suite'],
            [
                'base_price' => 850000.00,
                'capacity' => 4,
                'description' => 'Suite luas 2 kamar tidur, ruang santai keluarga, dapur mini, dan bathtub.',
            ]
        );

        // 2. Seed Physical Rooms
        $rooms = [
            // Lantai 1
            ['room_type_id' => $vip->id, 'room_number' => '101', 'status' => 'available', 'floor' => 'Lantai 1', 'notes' => 'Tamu check-in kemarin'],
            ['room_type_id' => $vip->id, 'room_number' => '102', 'status' => 'available', 'floor' => 'Lantai 1', 'notes' => 'Tamu baru saja check-out jam 12:00'],
            ['room_type_id' => $deluxe->id, 'room_number' => '103', 'status' => 'available', 'floor' => 'Lantai 1', 'notes' => null],
            ['room_type_id' => $deluxe->id, 'room_number' => '104', 'status' => 'available', 'floor' => 'Lantai 1', 'notes' => null],
            ['room_type_id' => $standard->id, 'room_number' => '105', 'status' => 'available', 'floor' => 'Lantai 1', 'notes' => null],
            ['room_type_id' => $standard->id, 'room_number' => '106', 'status' => 'available', 'floor' => 'Lantai 1', 'notes' => null],

            // Lantai 2
            ['room_type_id' => $deluxe->id, 'room_number' => '201', 'status' => 'available', 'floor' => 'Lantai 2', 'notes' => null],
            ['room_type_id' => $deluxe->id, 'room_number' => '202', 'status' => 'available', 'floor' => 'Lantai 2', 'notes' => null],
            ['room_type_id' => $standard->id, 'room_number' => '203', 'status' => 'available', 'floor' => 'Lantai 2', 'notes' => 'Perlu ganti sprei & handuk'],
            ['room_type_id' => $standard->id, 'room_number' => '204', 'status' => 'available', 'floor' => 'Lantai 2', 'notes' => null],
            ['room_type_id' => $standard->id, 'room_number' => '205', 'status' => 'available', 'floor' => 'Lantai 2', 'notes' => null],
            ['room_type_id' => $standard->id, 'room_number' => '206', 'status' => 'available', 'floor' => 'Lantai 2', 'notes' => 'Perbaikan AC & kran wastafel bocor'],
        ];

        foreach ($rooms as $room) {
            Room::firstOrCreate(
                ['room_number' => $room['room_number']],
                $room
            );
        }
    }
}

