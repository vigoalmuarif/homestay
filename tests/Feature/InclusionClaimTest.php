<?php

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Customer;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\RoomTypeServiceInclusion;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('receptionist');
});

test('claim desk returns 200 ok for authenticated user', function () {
    $user = $this->user;

    $response = $this->actingAs($user)->get(route('inclusions.claim-desk'));

    $response->assertStatus(200);
    $response->assertSee('Pusat Klaim Inklusi & Fulfillment');
});

test('quick claim creates zero-price delivered booking item', function () {
    $user = $this->user;

    $customer = Customer::create([
        'full_name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'phone_number' => '081234567890',
    ]);

    $roomType = RoomType::create([
        'name' => 'Deluxe Balcony Suite',
        'code' => 'DBS-01',
        'base_price' => 500000,
        'capacity_adult' => 2,
        'capacity_child' => 1,
    ]);

    $room = Room::create([
        'room_number' => '301',
        'room_type_id' => $roomType->id,
        'floor' => 'Lantai 3',
        'status' => 'occupied',
    ]);

    $booking = Booking::create([
        'booking_code' => 'BK-TEST-101',
        'customer_id' => $customer->id,
        'room_id' => $room->id,
        'check_in_date' => now()->startOfDay()->addHours(14),
        'check_out_date' => now()->addDays(2)->startOfDay()->addHours(12),
        'total_nights' => 2,
        'room_price_snapshot' => 500000,
        'status' => 'checked_in',
    ]);

    $breakfastService = Service::create([
        'name' => 'Paket Sarapan Nusantara',
        'category' => 'meal',
        'sub_category' => 'breakfast',
        'price' => 45000,
        'has_stock' => false,
        'is_active' => true,
    ]);

    $inclusion = RoomTypeServiceInclusion::create([
        'room_type_id' => $roomType->id,
        'service_id' => $breakfastService->id,
        'category' => 'meal',
        'session_time' => 'breakfast',
        'allowance_limit' => 0.00,
    ]);

    $response = $this->actingAs($user)->post(route('inclusions.quick-claim', $booking->id), [
        'inclusion_id' => $inclusion->id,
        'service_id' => $breakfastService->id,
        'quantity' => 2,
        'session_time' => 'breakfast',
        'notes' => 'Diantar ke kamar jam 07:30',
    ]);

    $response->assertSessionHas('success');

    $item = BookingItem::where('booking_id', $booking->id)->first();
    expect($item)->not->toBeNull();
    expect((float)$item->subtotal)->toBe(0.0);
    expect($item->status)->toBe('delivered');
    expect($item->is_free_inclusion)->toBeTrue();
});

test('fulfillment order creates pending task for laundry and can be marked as delivered', function () {
    $user = $this->user;

    $customer = Customer::create([
        'full_name' => 'Citra Dewi',
        'email' => 'citra@example.com',
        'phone_number' => '081298765432',
    ]);

    $roomType = RoomType::create([
        'name' => 'Family Suite Villa',
        'code' => 'FSV-01',
        'base_price' => 750000,
        'capacity_adult' => 4,
        'capacity_child' => 2,
    ]);

    $room = Room::create([
        'room_number' => '302',
        'room_type_id' => $roomType->id,
        'floor' => 'Lantai 3',
        'status' => 'occupied',
    ]);

    $booking = Booking::create([
        'booking_code' => 'BK-TEST-102',
        'customer_id' => $customer->id,
        'room_id' => $room->id,
        'check_in_date' => now()->startOfDay()->addHours(14),
        'check_out_date' => now()->addDays(3)->startOfDay()->addHours(12),
        'total_nights' => 3,
        'room_price_snapshot' => 750000,
        'status' => 'checked_in',
    ]);

    $laundryService = Service::create([
        'name' => 'Laundry Kilat 1 Hari',
        'category' => 'laundry',
        'sub_category' => 'daily',
        'price' => 35000,
        'has_stock' => false,
        'is_active' => true,
    ]);

    // 1. Submit fulfillment request
    $response = $this->actingAs($user)->post(route('inclusions.fulfillment-order', $booking->id), [
        'service_id' => $laundryService->id,
        'quantity' => 2,
        'category_type' => 'laundry',
        'detail_notes' => '4 kemeja batik, laundry bag LB-12',
    ]);

    $response->assertSessionHas('success');

    $item = BookingItem::where('booking_id', $booking->id)->first();
    expect($item)->not->toBeNull();
    expect($item->status)->toBe('pending');
    expect($item->notes)->toContain('LB-12');

    // 2. Mark task as delivered
    $updateResponse = $this->actingAs($user)->patch(route('inclusions.update-status', $item->id), [
        'status' => 'delivered',
        'action_note' => 'Selesai disetrika dan diantar ke kamar',
    ]);

    $updateResponse->assertSessionHas('success');
    expect($item->fresh()->status)->toBe('delivered');
});

test('claim desk can filter bookings and orders by date', function () {
    $user = $this->user;

    $response = $this->actingAs($user)->get(route('inclusions.claim-desk', [
        'date' => '2026-11-10',
    ]));

    $response->assertStatus(200);
    $response->assertSee('2026-11-10');
});

test('quick claim on past date persists past created_at and redirects with past date', function () {
    $user = $this->user;

    $customer = Customer::create([
        'full_name' => 'Past Guest',
        'email' => 'past@example.com',
        'phone_number' => '081233344455',
    ]);

    $roomType = RoomType::create([
        'name' => 'Past Suite',
        'code' => 'PS-01',
        'base_price' => 400000,
        'capacity_adult' => 2,
        'capacity_child' => 0,
    ]);

    $room = Room::create([
        'room_number' => '109',
        'room_type_id' => $roomType->id,
        'floor' => 'Lantai 1',
        'status' => 'occupied',
    ]);

    $pastDate = '2026-11-05';

    $booking = Booking::create([
        'booking_code' => 'BK-PAST-01',
        'customer_id' => $customer->id,
        'room_id' => $room->id,
        'check_in_date' => '2026-11-04 14:00:00',
        'check_out_date' => '2026-11-08 12:00:00',
        'total_nights' => 4,
        'room_price_snapshot' => 400000,
        'status' => 'checked_in',
    ]);

    $mealService = Service::create([
        'name' => 'Nasi Goreng Spesial',
        'category' => 'meal',
        'price' => 30000,
        'has_stock' => false,
        'is_active' => true,
    ]);

    $inclusion = RoomTypeServiceInclusion::create([
        'room_type_id' => $roomType->id,
        'service_id' => $mealService->id,
        'category' => 'meal',
        'allowance_limit' => 0.00,
    ]);

    $response = $this->actingAs($user)->post(route('inclusions.quick-claim', $booking->id), [
        'inclusion_id' => $inclusion->id,
        'service_id' => $mealService->id,
        'claim_date' => $pastDate,
    ]);

    $response->assertRedirect(route('inclusions.claim-desk', ['date' => $pastDate]));

    $item = BookingItem::where('booking_id', $booking->id)->first();
    expect($item)->not->toBeNull();
    expect($item->created_at->format('Y-m-d'))->toBe($pastDate);
});
