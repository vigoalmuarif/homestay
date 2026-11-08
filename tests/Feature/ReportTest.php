<?php

use App\Models\RoomType;
use App\Models\User;
use Carbon\Carbon;
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
});

test('unauthenticated users cannot access reports', function () {
    $response = $this->get(route('reports.index'));
    $response->assertRedirect(route('login'));
});

test('receptionist is forbidden from accessing reports without view reports permission', function () {
    $response = $this->actingAs($this->receptionist)->get(route('reports.index'));
    $response->assertStatus(403);
});

test('authorized manager/admin can view report index with kpi metrics and daily breakdown', function () {
    $response = $this->actingAs($this->admin)->get(route('reports.index'));

    $response->assertStatus(200);
    $response->assertSee('Laporan Rekapitulasi Finansial &amp; Operasional', false);
    $response->assertSee('Total Omzet Bruto');
    $response->assertSee('Sewa Kamar');
    $response->assertSee('Layanan &amp; Mini-POS', false);
    $response->assertSee('Rekapitulasi Omzet Harian');
    $response->assertSee('Rincian Transaksi Reservasi dalam Periode');
});

test('manager can filter report by preset and custom date range', function () {
    $start = Carbon::now()->subDays(7)->format('Y-m-d');
    $end = Carbon::now()->format('Y-m-d');

    $response = $this->actingAs($this->admin)->get(route('reports.index', [
        'preset' => 'custom',
        'start_date' => $start,
        'end_date' => $end,
    ]));

    $response->assertStatus(200);
    $response->assertSee($start);
    $response->assertSee($end);
});

test('manager can filter report by room type', function () {
    $roomType = RoomType::first();

    $response = $this->actingAs($this->admin)->get(route('reports.index', [
        'room_type_id' => $roomType->id,
    ]));

    $response->assertStatus(200);
});

test('manager can export date-filtered report to csv', function () {
    $response = $this->actingAs($this->admin)->get(route('reports.export-csv', [
        'preset' => 'this_month',
    ]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});
