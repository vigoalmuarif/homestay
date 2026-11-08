<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Superadmin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@homestay.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        // 2. Receptionist User
        $receptionist = User::firstOrCreate(
            ['email' => 'receptionist@homestay.test'],
            [
                'name' => 'Resepsionis Front Desk',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $receptionist->syncRoles(['receptionist']);

        // 3. Housekeeping User
        $housekeeping = User::firstOrCreate(
            ['email' => 'housekeeping@homestay.test'],
            [
                'name' => 'Staf Housekeeping',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $housekeeping->syncRoles(['housekeeping']);
    }
}

