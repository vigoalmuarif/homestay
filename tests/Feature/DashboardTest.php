<?php

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
});

test('unauthenticated users are redirected to login from dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});

test('authenticated user can view homestay operations dashboard', function () {
    $user = User::where('email', 'admin@homestay.test')->first();

    $response = $this->actingAs($user)->get('/');

    $response->assertStatus(200);
    $response->assertSee('Dashboard Operasional Homestay');
    $response->assertSee('Kamar Terisi (Okupansi)');
    $response->assertSee('Tamu Check-In Hari Ini');
    $response->assertSee('Kamar Kotor (Housekeeping)');
    $response->assertSee('Pendapatan Hari Ini');
    $response->assertSee('Tamu Check-In Hari Ini');
    $response->assertSee('Tamu Check-Out Hari Ini');
});

test('receptionist can view dashboard and arrivals list', function () {
    $user = User::where('email', 'receptionist@homestay.test')->first();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('Hendra Pratama');
    $response->assertSee('Sarah Wijaya');
    $response->assertSee('David Tanudjaya');
});

test('homestay module routes are accessible for authenticated users', function (string $route) {
    $user = User::where('email', 'admin@homestay.test')->first();

    $response = $this->actingAs($user)->get($route);

    $response->assertStatus(200);
})->with([
    '/calendar',
    '/bookings',
    '/customers',
    '/rooms',
    '/services',
    '/housekeeping',
    '/billing',
]);

