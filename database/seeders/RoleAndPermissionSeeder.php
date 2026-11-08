<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Permissions
        $permissions = [
            // Dashboard
            'view dashboard',

            // Master Customer
            'view customers',
            'manage customers',

            // Master Kamar & Tipe Kamar
            'view rooms',
            'manage rooms',
            'update room status',

            // Master Layanan & Katalog Mini-POS
            'view services',
            'manage services',

            // Front Desk & Reservasi
            'view bookings',
            'manage bookings',
            'checkin checkout',

            // Room Orders / Mini-POS Transactions
            'view room-orders',
            'manage room-orders',

            // Housekeeping Operations
            'view housekeeping',
            'manage housekeeping',

            // Billing & Invoicing
            'view billing',
            'process payments',

            // Reports & Analytics
            'view reports',

            // User Management
            'manage users',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // 2. Create Roles and Assign Permissions
        // Admin / Owner / Manager: All permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        // Receptionist / Front Desk
        $receptionistRole = Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => 'web']);
        $receptionistRole->syncPermissions([
            'view dashboard',
            'view customers',
            'manage customers',
            'view rooms',
            'update room status',
            'view services',
            'view bookings',
            'manage bookings',
            'checkin checkout',
            'view room-orders',
            'manage room-orders',
            'view housekeeping',
            'view billing',
            'process payments',
        ]);

        // Housekeeping
        $housekeepingRole = Role::firstOrCreate(['name' => 'housekeeping', 'guard_name' => 'web']);
        $housekeepingRole->syncPermissions([
            'view dashboard',
            'view rooms',
            'update room status',
            'view housekeeping',
            'manage housekeeping',
        ]);
    }
}

