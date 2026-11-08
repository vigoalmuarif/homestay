<?php

namespace Database\Seeders;

use App\Models\RoomType;
use App\Models\RoomTypeServiceInclusion;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Services & Mini-POS items
        $services = [
            // Meals
            [
                'name' => 'Nasi Goreng Spesial Homestay',
                'category' => 'meal',
                'sub_category' => 'breakfast',
                'price' => 30000.00,
                'has_stock' => false,
                'stock_quantity' => 0,
                'is_active' => true,
                'description' => 'Nasi goreng kampung dengan telur mata sapi, acar, dan kerupuk udang.',
            ],
            [
                'name' => 'American Breakfast (Roti Panggang & Omelet)',
                'category' => 'meal',
                'sub_category' => 'breakfast',
                'price' => 35000.00,
                'has_stock' => false,
                'stock_quantity' => 0,
                'is_active' => true,
                'description' => '2 lembar roti panggang, sosis panggang, omelet keju, dan selai.',
            ],
            [
                'name' => 'Mie Goreng Jawa',
                'category' => 'meal',
                'sub_category' => 'dinner',
                'price' => 25000.00,
                'has_stock' => false,
                'stock_quantity' => 0,
                'is_active' => true,
                'description' => 'Mie kuning goreng bumbu rempah tradisional Jawa dengan suwiran ayam.',
            ],

            // Beverages
            [
                'name' => 'Kopi Tubruk Robusta',
                'category' => 'beverage',
                'sub_category' => 'hot',
                'price' => 15000.00,
                'has_stock' => false,
                'stock_quantity' => 0,
                'is_active' => true,
                'description' => 'Kopi tubruk panas racikan biji kopi lokal fresh roast.',
            ],
            [
                'name' => 'Air Mineral 600ml',
                'category' => 'beverage',
                'sub_category' => 'cold',
                'price' => 7000.00,
                'has_stock' => true,
                'stock_quantity' => 48,
                'is_active' => true,
                'description' => 'Air mineral botol kemasan dingin / suhu ruang.',
            ],
            [
                'name' => 'Soft Drink Kaleng (Minibar)',
                'category' => 'beverage',
                'sub_category' => 'minibar',
                'price' => 15000.00,
                'has_stock' => true,
                'stock_quantity' => 24,
                'is_active' => true,
                'description' => 'Minuman bersoda dingin dalam minibar kamar.',
            ],

            // Amenities & Rentals
            [
                'name' => 'Extra Bed + Bantal Tambahan',
                'category' => 'amenity',
                'sub_category' => 'daily',
                'price' => 100000.00,
                'has_stock' => false,
                'stock_quantity' => 0,
                'is_active' => true,
                'description' => 'Kasur lipat single nyaman lengkap dengan sprei bersih dan selimut tebal.',
            ],
            [
                'name' => 'Rental Sepeda Motor Matic (24 Jam)',
                'category' => 'rental',
                'sub_category' => 'daily',
                'price' => 90000.00,
                'has_stock' => true,
                'stock_quantity' => 3,
                'is_active' => true,
                'description' => 'Sewa motor Honda Beat / Vario 125 termasuk 2 helm SNI dan jas hujan.',
            ],
            [
                'name' => 'Laundry Express (per Kg)',
                'category' => 'laundry',
                'sub_category' => 'wash_fold',
                'price' => 15000.00,
                'has_stock' => false,
                'stock_quantity' => 0,
                'is_active' => true,
                'description' => 'Cuci, kering, dan setrika rapi dalam waktu 8 jam.',
            ],
        ];

        foreach ($services as $data) {
            Service::firstOrCreate(['name' => $data['name']], $data);
        }

        // 2. Seed Package Inclusions per Room Type
        $standardType = RoomType::where('name', 'Standard Double')->first();
        $deluxeType = RoomType::where('name', 'Deluxe Balcony')->first();
        $vipType = RoomType::where('name', 'VIP Family Suite')->first();

        $nasiGoreng = Service::where('name', 'Nasi Goreng Spesial Homestay')->first();

        if ($standardType) {
            RoomTypeServiceInclusion::firstOrCreate([
                'room_type_id' => $standardType->id,
                'category' => 'meal',
                'session_time' => 'breakfast',
            ], [
                'allowance_limit' => 30000.00, // Disubsidi Rp 30.000 (menu > Rp 30rb kena selisih bayar)
            ]);
        }

        if ($deluxeType) {
            RoomTypeServiceInclusion::firstOrCreate([
                'room_type_id' => $deluxeType->id,
                'category' => 'meal',
                'session_time' => 'breakfast',
            ], [
                'allowance_limit' => 0.00, // 0.00 = Full cover gratis
            ]);

            RoomTypeServiceInclusion::firstOrCreate([
                'room_type_id' => $deluxeType->id,
                'category' => 'beverage',
                'session_time' => 'daily',
            ], [
                'allowance_limit' => 0.00,
            ]);
        }

        if ($vipType) {
            RoomTypeServiceInclusion::firstOrCreate([
                'room_type_id' => $vipType->id,
                'category' => 'meal',
                'session_time' => 'breakfast',
            ], [
                'allowance_limit' => 0.00,
            ]);

            RoomTypeServiceInclusion::firstOrCreate([
                'room_type_id' => $vipType->id,
                'category' => 'amenity',
                'session_time' => 'daily',
            ], [
                'allowance_limit' => 0.00,
            ]);
        }
    }
}

