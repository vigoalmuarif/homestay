<?php

namespace Database\Seeders;

use App\Models\OperationalTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OperationalTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@homestay.test')->first() ?? User::first();
        $receptionist = User::where('email', 'receptionist@homestay.test')->first() ?? $admin;
        $housekeeping = User::where('email', 'housekeeping@homestay.test')->first() ?? $admin;

        if (!$admin) {
            return;
        }

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $twoDaysAgo = Carbon::today()->subDays(2);

        $samples = [
            // Pemasukan Umum (General Income)
            [
                'type' => 'income',
                'category' => 'Parkir Tamu / Kendaraan',
                'title' => 'Parkir Bus Pariwisata Rombongan Tamu',
                'amount' => 50000,
                'transaction_date' => $today->format('Y-m-d'),
                'payment_method' => 'cash',
                'notes' => 'Tamu rombongan check-in 2 kamar family',
                'user_id' => $receptionist->id,
            ],
            [
                'type' => 'income',
                'category' => 'Titip Bagasi / Penitipan',
                'title' => 'Penitipan Koper Tamu Setelah Check-out',
                'amount' => 30000,
                'transaction_date' => $today->format('Y-m-d'),
                'payment_method' => 'cash',
                'notes' => 'Koper 3 pcs dititipkan hingga pukul 18.00 WIB',
                'user_id' => $receptionist->id,
            ],
            [
                'type' => 'income',
                'category' => 'Penjualan Kardus / Barang Bekas',
                'title' => 'Penjualan Kardus Bekas & Botol Mineral Kamar',
                'amount' => 75000,
                'transaction_date' => $yesterday->format('Y-m-d'),
                'payment_method' => 'cash',
                'notes' => 'Hasil pemilahan kardus oleh staf housekeeping',
                'user_id' => $housekeeping->id,
            ],
            [
                'type' => 'income',
                'category' => 'Denda Kerusakan & Kehilangan',
                'title' => 'Ganti Rugi Pecah Gelas Kaca Kamar 204',
                'amount' => 25000,
                'transaction_date' => $twoDaysAgo->format('Y-m-d'),
                'payment_method' => 'cash',
                'notes' => 'Gelas pecah saat tamu menginap, diganti tunai',
                'user_id' => $housekeeping->id,
            ],

            // Pengeluaran Umum (General Expense)
            [
                'type' => 'expense',
                'category' => 'Bahan Pembersih & Deterjen',
                'title' => 'Beli Deterjen Bubuk 5kg & Cairan Pel Lantai',
                'amount' => 85000,
                'transaction_date' => $today->format('Y-m-d'),
                'payment_method' => 'cash',
                'notes' => 'Stok kebersihan mingguan housekeeping lantai 1 & 2',
                'user_id' => $housekeeping->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Galon Air Minum & Gas',
                'title' => 'Isi Ulang 4 Galon Air Minum Dispenser Tamu',
                'amount' => 76000,
                'transaction_date' => $today->format('Y-m-d'),
                'payment_method' => 'cash',
                'notes' => 'Untuk dispenser lobby dan area pantry tamu',
                'user_id' => $receptionist->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Perbaikan & Pemeliharaan (Maintenance)',
                'title' => 'Beli 3 Pcs Bohlam LED 12W Kamar 103 & Koridor',
                'amount' => 65000,
                'transaction_date' => $yesterday->format('Y-m-d'),
                'payment_method' => 'cash',
                'notes' => 'Bohlam kamar redup dan putus, diganti baru',
                'user_id' => $housekeeping->id,
            ],
            [
                'type' => 'expense',
                'category' => 'Amenities & Perlengkapan Kamar',
                'title' => 'Beli Plastik Sampah Roll Hitam & Pewangi Kamar Mandi',
                'amount' => 45000,
                'transaction_date' => $twoDaysAgo->format('Y-m-d'),
                'payment_method' => 'cash',
                'notes' => 'Kebutuhan perlengkapan kebersihan kamar homestay',
                'user_id' => $housekeeping->id,
            ],
            [
                'type' => 'expense',
                'category' => 'ATK & Perlengkapan Kantor',
                'title' => 'Kertas Thermal Roll Struk Kasir & Pulpen',
                'amount' => 38000,
                'transaction_date' => $twoDaysAgo->format('Y-m-d'),
                'payment_method' => 'cash',
                'notes' => 'Kebutuhan administrasi front desk resepsionis',
                'user_id' => $receptionist->id,
            ],
        ];

        foreach ($samples as $item) {
            OperationalTransaction::create($item);
        }
    }
}

