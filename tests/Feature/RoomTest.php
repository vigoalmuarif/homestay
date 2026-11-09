<?php

use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\RoomSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(UserSeeder::class);
    $this->seed(CustomerSeeder::class);
    $this->seed(RoomSeeder::class);

    $this->admin = User::where('email', 'admin@homestay.test')->first();
    $this->receptionist = User::where('email', 'receptionist@homestay.test')->first();
    $this->housekeeping = User::where('email', 'housekeeping@homestay.test')->first();
});

test('authenticated user can view room types list', function () {
    $response = $this->actingAs($this->admin)->get(route('room-types.index'));

    $response->assertStatus(200);
    $response->assertSee('Tipe Kamar &amp; Paket', false);
    $response->assertSee('Standard Double');
    $response->assertSee('Deluxe Balcony');
    $response->assertSee('VIP Family Suite');
});

test('room type can be created with valid data', function () {
    $response = $this->actingAs($this->admin)->post(route('room-types.store'), [
        'name' => 'Presidential Penthouse',
        'base_price' => 1500000,
        'capacity' => 6,
        'description' => 'Lantai teratas dengan kolam renang mini pribadi.',
    ]);

    $response->assertRedirect(route('room-types.index'));
    $this->assertDatabaseHas('room_types', [
        'name' => 'Presidential Penthouse',
        'base_price' => 1500000.00,
    ]);
});

test('room type can be updated', function () {
    $type = RoomType::where('name', 'Standard Double')->first();

    $response = $this->actingAs($this->admin)->put(route('room-types.update', $type), [
        'name' => 'Standard Double Queen',
        'base_price' => 375000,
        'capacity' => 2,
    ]);

    $response->assertRedirect(route('room-types.index'));
    $this->assertEquals('Standard Double Queen', $type->fresh()->name);
});

test('room type deletion is prevented if active rooms exist', function () {
    $type = RoomType::where('name', 'Standard Double')->first();

    $response = $this->actingAs($this->admin)->delete(route('room-types.destroy', $type));

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('room_types', ['id' => $type->id]);
});

test('authenticated user can view physical rooms list with counter stats', function () {
    $response = $this->actingAs($this->admin)->get(route('rooms.index'));

    $response->assertStatus(200);
    $response->assertSee('Master Unit Kamar Fisik');
    $response->assertSee('101');
    $response->assertSee('102');
    $response->assertSee('Total Kamar');
});

test('rooms can be filtered by status', function () {
    $response = $this->actingAs($this->admin)->get(route('rooms.index', ['status' => 'dirty']));

    $response->assertStatus(200);
    $response->assertSee('102');
    $response->assertSee('203');
    $response->assertDontSee('103'); // 103 is available
});

test('physical room can be created', function () {
    $type = RoomType::first();

    $response = $this->actingAs($this->admin)->post(route('rooms.store'), [
        'room_type_id' => $type->id,
        'room_number' => '301',
        'status' => 'available',
        'floor' => 'Lantai 3',
        'notes' => 'Kamar sudut baru',
    ]);

    $response->assertRedirect(route('rooms.index'));
    $this->assertDatabaseHas('rooms', [
        'room_number' => '301',
        'status' => 'available',
    ]);
});

test('duplicate room number is rejected', function () {
    $existing = Room::first();

    $response = $this->actingAs($this->admin)->post(route('rooms.store'), [
        'room_type_id' => $existing->room_type_id,
        'room_number' => $existing->room_number,
        'status' => 'available',
    ]);

    $response->assertSessionHasErrors('room_number');
});

test('fast status switcher allows updating room status', function () {
    $room = Room::where('status', 'dirty')->first() ?? Room::first();
    $this->assertNotNull($room);

    $response = $this->actingAs($this->housekeeping)->post(route('rooms.update-status', $room), [
        'status' => 'available',
    ]);

    $response->assertRedirect();
    $this->assertEquals('available', $room->fresh()->status);
});

test('fast status switcher supports json response', function () {
    $room = Room::where('status', 'available')->first();

    $response = $this->actingAs($this->receptionist)->postJson(route('rooms.update-status', $room), [
        'status' => 'occupied',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'data' => [
            'id' => $room->id,
            'status' => 'occupied',
        ],
    ]);
});

test('physical room can be deleted', function () {
    $room = Room::where('room_number', '206')->first();

    $response = $this->actingAs($this->admin)->delete(route('rooms.destroy', $room));

    $response->assertRedirect(route('rooms.index'));
    $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
});

