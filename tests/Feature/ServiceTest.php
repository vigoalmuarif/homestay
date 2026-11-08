<?php

use App\Models\RoomType;
use App\Models\RoomTypeServiceInclusion;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\RoomSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(UserSeeder::class);
    $this->seed(RoomSeeder::class);
    $this->seed(ServiceSeeder::class);

    $this->admin = User::where('email', 'admin@homestay.test')->first();
    $this->receptionist = User::where('email', 'receptionist@homestay.test')->first();
});

test('unauthenticated user cannot access service catalog', function () {
    $response = $this->get(route('services.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated staff can view services catalog', function () {
    $response = $this->actingAs($this->receptionist)->get(route('services.index'));

    $response->assertStatus(200);
    $response->assertSee('Katalog Layanan, F&amp;B &amp; Mini-POS', false);
    $response->assertSee('Nasi Goreng Spesial');
    $response->assertSee('Air Mineral 600ml');
});

test('new service can be added with stock tracking', function () {
    $response = $this->actingAs($this->admin)->post(route('services.store'), [
        'name' => 'Keripik Tempe Renyah',
        'category' => 'meal',
        'sub_category' => 'snack',
        'price' => 15000,
        'has_stock' => 1,
        'stock_quantity' => 25,
        'is_active' => 1,
        'description' => 'Camilan khas oleh-oleh lokal.',
    ]);

    $response->assertRedirect(route('services.index'));
    $this->assertDatabaseHas('services', [
        'name' => 'Keripik Tempe Renyah',
        'price' => 15000.00,
        'has_stock' => 1,
        'stock_quantity' => 25,
    ]);
});

test('service validation requires valid name and non-negative price', function () {
    $response = $this->actingAs($this->admin)->post(route('services.store'), [
        'name' => '',
        'category' => 'invalid_cat',
        'price' => -5000,
    ]);

    $response->assertSessionHasErrors(['name', 'category', 'price']);
});

test('service can be updated', function () {
    $service = Service::where('name', 'Nasi Goreng Spesial Homestay')->first();
    $this->assertNotNull($service);

    $response = $this->actingAs($this->admin)->put(route('services.update', $service), [
        'name' => 'Nasi Goreng Spesial Kampung',
        'category' => 'meal',
        'sub_category' => 'breakfast',
        'price' => 38000,
        'has_stock' => 0,
        'stock_quantity' => 0,
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('services.index'));
    $this->assertEquals('Nasi Goreng Spesial Kampung', $service->fresh()->name);
    $this->assertEquals('38000.00', (string)$service->fresh()->price);
});

test('service active status can be toggled', function () {
    $service = Service::where('name', 'Nasi Goreng Spesial Homestay')->first();
    $this->assertNotNull($service);
    $this->assertTrue((bool)$service->is_active);

    $response = $this->actingAs($this->admin)->post(route('services.toggle-active', $service));
    $response->assertSessionHas('success');
    $this->assertFalse((bool)$service->fresh()->is_active);

    $this->actingAs($this->admin)->post(route('services.toggle-active', $service));
    $this->assertTrue((bool)$service->fresh()->is_active);
});

test('service search endpoint returns json list of active services', function () {
    $response = $this->actingAs($this->receptionist)->getJson(route('services.search', ['q' => 'Goreng']));

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'name' => 'Nasi Goreng Spesial Homestay',
    ]);
});

test('service search endpoint supports select2 server-side format with category filter', function () {
    $response = $this->actingAs($this->receptionist)->getJson(route('services.search', [
        'q' => 'Goreng',
        'category' => 'meal',
        'format' => 'select2',
    ]));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'results' => [
            '*' => ['id', 'text', 'name', 'category', 'price', 'has_stock', 'stock_quantity'],
        ],
    ]);
});

test('authenticated staff can view package inclusions matrix', function () {
    $response = $this->actingAs($this->admin)->get(route('service-inclusions.index'));

    $response->assertStatus(200);
    $response->assertSee('Pengaturan Inklusi Paket Tipe Kamar');
    $response->assertSee('VIP Family Suite');
});

test('inclusion rule can be created for a room type', function () {
    $roomType = RoomType::where('name', 'Standard Double')->first();
    $service = Service::where('name', 'Nasi Goreng Spesial Homestay')->first();
    $this->assertNotNull($roomType);
    $this->assertNotNull($service);

    $response = $this->actingAs($this->admin)->post(route('service-inclusions.store'), [
        'room_type_id' => $roomType->id,
        'category' => 'meal',
        'service_id' => $service->id,
        'session_time' => 'breakfast',
        'allowance_limit' => 0,
    ]);

    $response->assertRedirect(route('service-inclusions.index'));
    $this->assertDatabaseHas('room_type_service_inclusions', [
        'room_type_id' => $roomType->id,
        'service_id' => $service->id,
        'category' => 'meal',
        'session_time' => 'breakfast',
    ]);
});

test('inclusion rule can be deleted', function () {
    $inclusion = RoomTypeServiceInclusion::first();
    $this->assertNotNull($inclusion);

    $response = $this->actingAs($this->admin)->delete(route('service-inclusions.destroy', $inclusion));
    $response->assertRedirect(route('service-inclusions.index'));
    $this->assertDatabaseMissing('room_type_service_inclusions', [
        'id' => $inclusion->id,
    ]);
});
