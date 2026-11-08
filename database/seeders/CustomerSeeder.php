<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'full_name' => 'Hendra Pratama',
                'identity_type' => 'ktp',
                'identity_number' => '3201123456780001',
                'phone_number' => '081234567890',
                'email' => 'hendra.pratama@gmail.com',
                'address' => 'Jl. Tebet Barat Dalam No. 14, Jakarta Selatan',
                'country' => 'Indonesia',
                'special_notes' => 'Tamu langganan VIP, preferensi ekstra bantal & lantai bawah.',
                'is_blacklisted' => false,
            ],
            [
                'full_name' => 'Sarah Wijaya',
                'identity_type' => 'ktp',
                'identity_number' => '3175987654320002',
                'phone_number' => '085712349900',
                'email' => 'sarah.wijaya@outlook.com',
                'address' => 'Jl. Dago Asri No. 8, Bandung',
                'country' => 'Indonesia',
                'special_notes' => 'Alergi kacang-kacangan pada sajian sarapan.',
                'is_blacklisted' => false,
            ],
            [
                'full_name' => 'David Tanudjaya',
                'identity_type' => 'paspor',
                'identity_number' => 'E78965431',
                'phone_number' => '081122334455',
                'email' => 'david.tan@globaltravel.sg',
                'address' => '10 Orchard Blvd, Singapore',
                'country' => 'Singapura',
                'special_notes' => 'Meminta layanan jemputan stasiun/bandara jika tiba larut malam.',
                'is_blacklisted' => false,
            ],
            [
                'full_name' => 'Budi Santoso',
                'identity_type' => 'sim',
                'identity_number' => 'SIM-9988771122',
                'phone_number' => '087899887766',
                'email' => 'budi.santoso@yahoo.com',
                'address' => 'Jl. Manyar Kertoarjo No. 25, Surabaya',
                'country' => 'Indonesia',
                'special_notes' => null,
                'is_blacklisted' => false,
            ],
            [
                'full_name' => 'Agus Permana',
                'identity_type' => 'ktp',
                'identity_number' => '3301889922110003',
                'phone_number' => '082199884433',
                'email' => 'agus.permana@badguest.test',
                'address' => 'Jl. Malioboro No. 99, Yogyakarta',
                'country' => 'Indonesia',
                'special_notes' => 'PERINGATAN: Pernah merusak fasilitas TV kamar & meninggalkan homestay tanpa pelunasan. JANGAN TERIMA RESERVASI.',
                'is_blacklisted' => true,
            ],
            [
                'full_name' => 'Maya Melinda',
                'identity_type' => 'ktp',
                'identity_number' => '3273112233440005',
                'phone_number' => '081399881122',
                'email' => 'maya.melinda@gmail.com',
                'address' => 'Jl. Pahlawan No. 12, Semarang',
                'country' => 'Indonesia',
                'special_notes' => 'Suka memesan room service kopi pagi hari.',
                'is_blacklisted' => false,
            ],
        ];

        foreach ($customers as $data) {
            Customer::firstOrCreate(
                ['identity_number' => $data['identity_number']],
                $data
            );
        }
    }
}

