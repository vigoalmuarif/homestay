<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view operational',
            'create operational',
            'manage operational',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Admin: all permissions
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        // Receptionist: view, create, manage
        $receptionist = Role::where('name', 'receptionist')->first();
        if ($receptionist) {
            $receptionist->givePermissionTo(['view operational', 'create operational', 'manage operational']);
        }

        // Housekeeping: view, create
        $housekeeping = Role::where('name', 'housekeeping')->first();
        if ($housekeeping) {
            $housekeeping->givePermissionTo(['view operational', 'create operational']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::whereIn('name', [
            'view operational',
            'create operational',
            'manage operational',
        ])->delete();
    }
};

