<?php

use App\Helpers\MenuHelper;
use App\Models\User;
use Database\Seeders\BookingSeeder;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\RoomSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(UserSeeder::class);
    $this->seed(CustomerSeeder::class);
    $this->seed(RoomSeeder::class);
    $this->seed(ServiceSeeder::class);
    $this->seed(BookingSeeder::class);

    $this->admin = User::where('email', 'admin@homestay.test')->first();
    $this->receptionist = User::where('email', 'receptionist@homestay.test')->first();
    $this->housekeeping = User::where('email', 'housekeeping@homestay.test')->first();
});

test('super admin has unrestricted access to all homestay modules', function () {
    $this->actingAs($this->admin);

    $routes = [
        '/',
        '/dashboard',
        '/calendar',
        '/bookings',
        '/customers',
        '/rooms',
        '/room-types',
        '/services',
        '/service-inclusions',
        '/inclusions/claim-desk',
        '/housekeeping',
        '/billing',
        '/reports',
        '/users',
    ];

    foreach ($routes as $route) {
        $response = $this->get($route);
        $response->assertStatus(200);
    }
});

test('receptionist can access operations and billing but is restricted from management reports and users', function () {
    $this->actingAs($this->receptionist);

    // Allowed routes for receptionist
    $allowedRoutes = [
        '/',
        '/dashboard',
        '/calendar',
        '/bookings',
        '/customers',
        '/rooms',
        '/services',
        '/inclusions/claim-desk',
        '/housekeeping',
        '/billing',
    ];

    foreach ($allowedRoutes as $route) {
        $response = $this->get($route);
        $response->assertStatus(200);
    }

    // Forbidden routes for receptionist (403 Forbidden)
    $forbiddenRoutes = [
        '/reports',
        '/room-types',
        '/service-inclusions',
        '/users',
    ];

    foreach ($forbiddenRoutes as $route) {
        $response = $this->get($route);
        $response->assertStatus(403);
    }
});

test('receptionist cannot modify master services or create room types', function () {
    $this->actingAs($this->receptionist);

    $response = $this->post('/room-types', [
        'name' => 'Presidential Suite',
        'base_price' => 1500000,
        'capacity' => 4,
    ]);
    $response->assertStatus(403);

    $response = $this->post('/services', [
        'name' => 'Layanan Ilegal',
        'category' => 'other',
        'price' => 100000,
    ]);
    $response->assertStatus(403);
});

test('housekeeping can access rooms and housekeeping but is blocked from bookings, billing and customer data', function () {
    $this->actingAs($this->housekeeping);

    // Allowed routes for housekeeping
    $allowedRoutes = [
        '/',
        '/dashboard',
        '/rooms',
        '/housekeeping',
    ];

    foreach ($allowedRoutes as $route) {
        $response = $this->get($route);
        $response->assertStatus(200);
    }

    // Forbidden routes for housekeeping (403 Forbidden)
    $forbiddenRoutes = [
        '/calendar',
        '/bookings',
        '/customers',
        '/room-types',
        '/services',
        '/service-inclusions',
        '/inclusions/claim-desk',
        '/billing',
        '/reports',
        '/users',
    ];

    foreach ($forbiddenRoutes as $route) {
        $response = $this->get($route);
        $response->assertStatus(403);
    }
});

test('sidebar navigation automatically filters menu items based on authenticated user role', function () {
    // 1. Housekeeping Menu
    $this->actingAs($this->housekeeping);
    $housekeepingMenu = MenuHelper::getMainNavItems();
    $housekeepingNames = collect($housekeepingMenu)->pluck('name')->all();

    expect($housekeepingNames)->toContain('Dashboard');
    expect($housekeepingNames)->toContain('Kamar & Tipe Kamar');
    expect($housekeepingNames)->toContain('Housekeeping');
    expect($housekeepingNames)->not->toContain('Reservasi & Front Desk');
    expect($housekeepingNames)->not->toContain('Data Tamu (Customer)');
    expect($housekeepingNames)->not->toContain('Layanan & F&B');
    expect($housekeepingNames)->not->toContain('Keuangan & Laporan');
    expect($housekeepingNames)->not->toContain('Kelola Petugas & Role');

    // 2. Receptionist Menu
    $this->actingAs($this->receptionist);
    $receptionistMenu = MenuHelper::getMainNavItems();
    $receptionistNames = collect($receptionistMenu)->pluck('name')->all();

    expect($receptionistNames)->toContain('Dashboard');
    expect($receptionistNames)->toContain('Kalender Okupansi');
    expect($receptionistNames)->toContain('Reservasi & Front Desk');
    expect($receptionistNames)->toContain('Data Tamu (Customer)');
    expect($receptionistNames)->toContain('Kamar & Tipe Kamar');
    expect($receptionistNames)->toContain('Layanan & F&B');
    expect($receptionistNames)->toContain('Housekeeping');
    expect($receptionistNames)->toContain('Keuangan & Laporan');
    expect($receptionistNames)->not->toContain('Kelola Petugas & Role');

    // Check that inside "Kamar & Tipe Kamar", receptionist only sees "Daftar Kamar Fisik" (not "Katalog Tipe Kamar")
    $kamarItem = collect($receptionistMenu)->firstWhere('name', 'Kamar & Tipe Kamar');
    $subNames = collect($kamarItem['subItems'])->pluck('name')->all();
    expect($subNames)->toContain('Daftar Kamar Fisik');
    expect($subNames)->not->toContain('Katalog Tipe Kamar');

    // Check that inside "Keuangan & Laporan", receptionist only sees "Folio Billing & Kasir" (not "Laporan Rekapitulasi")
    $keuanganItem = collect($receptionistMenu)->firstWhere('name', 'Keuangan & Laporan');
    $keuanganSubNames = collect($keuanganItem['subItems'])->pluck('name')->all();
    expect($keuanganSubNames)->toContain('Folio Billing & Kasir');
    expect($keuanganSubNames)->not->toContain('Laporan Rekapitulasi');
});

