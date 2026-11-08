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

    $this->customer = Customer::create([
        'full_name' => 'Budi Santoso',
        'identity_type' => 'ktp',
        'identity_number' => '3271012345678901',
        'phone_number' => '081234567890',
        'email' => 'budi@example.com',
        'is_blacklisted' => false,
    ]);

    $this->room = Room::with('roomType')->first();

    $this->booking = Booking::create([
        'booking_code' => Booking::generateCode(),
        'customer_id' => $this->customer->id,
        'room_id' => $this->room->id,
        'check_in_date' => now()->toDateString(),
        'check_out_date' => now()->addDays(2)->toDateString(),
        'total_nights' => 2,
        'room_price_snapshot' => $this->room->roomType->base_price,
        'deposit_amount' => 100000,
        'status' => 'checked_in',
        'payment_status' => 'unpaid',
    ]);
});

test('unauthenticated user cannot place room order', function () {
    $service = Service::first();

    $response = $this->post(route('bookings.orders.store', $this->booking), [
        'service_id' => $service->id,
        'quantity' => 1,
        'billing_type' => 'charged_to_room',
    ]);

    $response->assertRedirect(route('login'));
});

test('receptionist can place room order with atomic stock deduction', function () {
    $service = Service::where('has_stock', true)->where('stock_quantity', '>', 5)->first();
    $initialStock = $service->stock_quantity;

    $response = $this->actingAs($this->receptionist)->post(route('bookings.orders.store', $this->booking), [
        'service_id' => $service->id,
        'quantity' => 2,
        'billing_type' => 'charged_to_room',
        'notes' => 'Dingin, tanpa sedotan',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('booking_items', [
        'booking_id' => $this->booking->id,
        'service_id' => $service->id,
        'quantity' => 2,
        'billing_type' => 'charged_to_room',
        'notes' => 'Dingin, tanpa sedotan',
    ]);

    $this->assertEquals($initialStock - 2, $service->fresh()->stock_quantity);
});

test('order is rejected when requested quantity exceeds available stock', function () {
    $service = Service::where('has_stock', true)->first();
    $service->update(['stock_quantity' => 2]);

    $response = $this->actingAs($this->receptionist)->post(route('bookings.orders.store', $this->booking), [
        'service_id' => $service->id,
        'quantity' => 5, // exceeds available stock 2
        'billing_type' => 'charged_to_room',
    ]);

    $response->assertSessionHasErrors(['error']);
    $this->assertEquals(2, $service->fresh()->stock_quantity);
    $this->assertDatabaseMissing('booking_items', [
        'booking_id' => $this->booking->id,
        'service_id' => $service->id,
        'quantity' => 5,
    ]);
});

test('complimentary room package inclusion is calculated as zero cost', function () {
    // Create breakfast service
    $breakfast = Service::create([
        'name' => 'Paket Sarapan Pagi',
        'category' => 'meal',
        'sub_category' => 'breakfast',
        'price' => 35000,
        'has_stock' => false,
        'stock_quantity' => 0,
        'is_active' => true,
    ]);

    // Create free inclusion for this room type
    RoomTypeServiceInclusion::create([
        'room_type_id' => $this->room->room_type_id,
        'service_id' => $breakfast->id,
        'category' => 'meal',
        'session_time' => 'breakfast',
        'allowance_limit' => 0.00, // 0 = fully covered
    ]);

    $response = $this->actingAs($this->receptionist)->post(route('bookings.orders.store', $this->booking), [
        'service_id' => $breakfast->id,
        'quantity' => 2,
        'billing_type' => 'charged_to_room',
    ]);

    $response->assertSessionHasNoErrors();

    $item = BookingItem::where('booking_id', $this->booking->id)->where('service_id', $breakfast->id)->first();
    $this->assertNotNull($item);
    $this->assertTrue((bool)$item->is_free_inclusion);
    $this->assertEquals(0.00, (float)$item->subtotal);
    $this->assertEquals(70000.00, (float)$item->discount_or_allowance);
});

test('order can be paid instantly without adding to room balance', function () {
    $service = Service::create([
        'name' => 'Kopi Tubruk',
        'category' => 'beverage',
        'price' => 10000,
        'has_stock' => false,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->receptionist)->post(route('bookings.orders.store', $this->booking), [
        'service_id' => $service->id,
        'quantity' => 1,
        'billing_type' => 'paid_instantly',
    ]);

    $response->assertSessionHasNoErrors();

    $item = BookingItem::where('booking_id', $this->booking->id)->where('service_id', $service->id)->first();
    $this->assertEquals('paid_instantly', $item->billing_type);

    // Paid instantly should NOT increase total extra charges on room folio
    $this->assertEquals(0.00, $this->booking->fresh()->total_extra_charges);
});

test('cancelling a room order restores physical stock', function () {
    $service = Service::where('has_stock', true)->where('stock_quantity', '>', 5)->first();
    $initialStock = $service->stock_quantity;

    // Place order
    $this->actingAs($this->receptionist)->post(route('bookings.orders.store', $this->booking), [
        'service_id' => $service->id,
        'quantity' => 3,
        'billing_type' => 'charged_to_room',
    ]);

    $this->assertEquals($initialStock - 3, $service->fresh()->stock_quantity);

    $item = BookingItem::where('booking_id', $this->booking->id)->where('service_id', $service->id)->first();

    // Cancel order
    $deleteResponse = $this->actingAs($this->receptionist)->delete(route('bookings.orders.destroy', [$this->booking, $item]));
    $deleteResponse->assertSessionHasNoErrors();

    $this->assertEquals('cancelled', $item->fresh()->status);
    $this->assertEquals($initialStock, $service->fresh()->stock_quantity);
});

test('breakfast inclusion can only be claimed once per day and subsequent orders are billed regularly', function () {
    $breakfast = Service::create([
        'name' => 'Nasi Uduk Betawi',
        'category' => 'meal',
        'sub_category' => 'breakfast',
        'price' => 25000,
        'has_stock' => false,
        'is_active' => true,
    ]);

    RoomTypeServiceInclusion::create([
        'room_type_id' => $this->room->room_type_id,
        'service_id' => $breakfast->id,
        'category' => 'meal',
        'session_time' => 'breakfast',
        'allowance_limit' => 0.00,
    ]);

    // First claim today -> Free
    $firstResponse = $this->actingAs($this->receptionist)->post(route('bookings.orders.store', $this->booking), [
        'service_id' => $breakfast->id,
        'quantity' => 1,
        'billing_type' => 'charged_to_room',
    ]);
    $firstResponse->assertSessionHasNoErrors();

    $firstItem = BookingItem::where('booking_id', $this->booking->id)
        ->where('service_id', $breakfast->id)
        ->first();
    $this->assertTrue((bool)$firstItem->is_free_inclusion);
    $this->assertEquals(0.00, (float)$firstItem->subtotal);

    // Second order today of the same inclusion -> Billed at regular price (25,000)
    $secondResponse = $this->actingAs($this->receptionist)->post(route('bookings.orders.store', $this->booking), [
        'service_id' => $breakfast->id,
        'quantity' => 1,
        'billing_type' => 'charged_to_room',
    ]);
    $secondResponse->assertSessionHasNoErrors();

    $secondItem = BookingItem::where('booking_id', $this->booking->id)
        ->where('service_id', $breakfast->id)
        ->latest('id')
        ->first();
    $this->assertFalse((bool)$secondItem->is_free_inclusion);
    $this->assertEquals(25000.00, (float)$secondItem->subtotal);

    // Cancel first order -> Daily quota is freed
    $this->actingAs($this->receptionist)->delete(route('bookings.orders.destroy', [$this->booking, $firstItem]));
    $this->assertEquals('cancelled', $firstItem->fresh()->status);

    // Third order after cancellation -> Free again
    $thirdResponse = $this->actingAs($this->receptionist)->post(route('bookings.orders.store', $this->booking), [
        'service_id' => $breakfast->id,
        'quantity' => 1,
        'billing_type' => 'charged_to_room',
    ]);
    $thirdResponse->assertSessionHasNoErrors();

    $thirdItem = BookingItem::where('booking_id', $this->booking->id)
        ->where('service_id', $breakfast->id)
        ->latest('id')
        ->first();
    $this->assertTrue((bool)$thirdItem->is_free_inclusion);
    $this->assertEquals(0.00, (float)$thirdItem->subtotal);
});

test('partial allowance inclusion consumes daily quota and repeated claim attempt is rejected', function () {
    // Clear any seeded inclusions for this room type first
    RoomTypeServiceInclusion::where('room_type_id', $this->room->room_type_id)->delete();

    $breakfast = Service::create([
        'name' => 'American Breakfast Deluxe',
        'category' => 'meal',
        'sub_category' => 'breakfast',
        'price' => 35000,
        'has_stock' => false,
        'is_active' => true,
    ]);

    RoomTypeServiceInclusion::create([
        'room_type_id' => $this->room->room_type_id,
        'service_id' => null, // Category matching inclusion
        'category' => 'meal',
        'session_time' => 'breakfast',
        'allowance_limit' => 30000.00, // Rp 30.000 allowance (partial)
    ]);

    // First claim: notes contains 'Klaim Inklusi: Sarapan Pagi'
    $firstResponse = $this->actingAs($this->receptionist)->post(route('bookings.orders.store', $this->booking), [
        'service_id' => $breakfast->id,
        'quantity' => 1,
        'billing_type' => 'charged_to_room',
        'notes' => 'Klaim Inklusi: Sarapan Pagi (Breakfast)',
    ]);
    $firstResponse->assertSessionHasNoErrors();

    $item = BookingItem::where('booking_id', $this->booking->id)
        ->where('service_id', $breakfast->id)
        ->first();
    $this->assertFalse((bool)$item->is_free_inclusion);
    $this->assertEquals(30000.00, (float)$item->discount_or_allowance);
    $this->assertEquals(5000.00, (float)$item->subtotal);

    // Second claim attempt with Klaim Inklusi note should be rejected
    $secondResponse = $this->actingAs($this->receptionist)->post(route('bookings.orders.store', $this->booking), [
        'service_id' => $breakfast->id,
        'quantity' => 1,
        'billing_type' => 'charged_to_room',
        'notes' => 'Klaim Inklusi: Sarapan Pagi (Breakfast)',
    ]);
    $secondResponse->assertSessionHasErrors(['error']);

    // Second regular order without claim note should succeed at full price without allowance
    $regularResponse = $this->actingAs($this->receptionist)->post(route('bookings.orders.store', $this->booking), [
        'service_id' => $breakfast->id,
        'quantity' => 1,
        'billing_type' => 'charged_to_room',
        'notes' => 'Porsi ekstra untuk tamu',
    ]);
    $regularResponse->assertSessionHasNoErrors();

    $regularItem = BookingItem::where('booking_id', $this->booking->id)
        ->where('service_id', $breakfast->id)
        ->latest('id')
        ->first();
    $this->assertEquals(0.00, (float)$regularItem->discount_or_allowance);
    $this->assertEquals(35000.00, (float)$regularItem->subtotal);
});

test('claiming breakfast does not consume lunch inclusion quota', function () {
    RoomTypeServiceInclusion::where('room_type_id', $this->room->room_type_id)->delete();

    $breakfast = Service::create([
        'name' => 'Nasi Goreng Breakfast',
        'category' => 'meal',
        'sub_category' => 'breakfast',
        'price' => 30000,
        'has_stock' => false,
        'is_active' => true,
    ]);

    $lunch = Service::create([
        'name' => 'Mie Goreng Lunch',
        'category' => 'meal',
        'sub_category' => 'lunch',
        'price' => 30000,
        'has_stock' => false,
        'is_active' => true,
    ]);

    // Inclusion 1: Breakfast
    RoomTypeServiceInclusion::create([
        'room_type_id' => $this->room->room_type_id,
        'service_id' => null,
        'category' => 'meal',
        'session_time' => 'breakfast',
        'allowance_limit' => 0.00,
    ]);

    // Inclusion 2: Lunch specifically for $lunch
    RoomTypeServiceInclusion::create([
        'room_type_id' => $this->room->room_type_id,
        'service_id' => $lunch->id,
        'category' => 'meal',
        'session_time' => 'lunch',
        'allowance_limit' => 0.00,
    ]);

    // Claim Breakfast
    $bfResponse = $this->actingAs($this->receptionist)->post(route('bookings.orders.store', $this->booking), [
        'service_id' => $breakfast->id,
        'quantity' => 1,
        'billing_type' => 'charged_to_room',
        'notes' => 'Klaim Inklusi: Sarapan Pagi (Breakfast)',
    ]);
    $bfResponse->assertSessionHasNoErrors();

    // Lunch claim should STILL SUCCEED and be free
    $lunchResponse = $this->actingAs($this->receptionist)->post(route('bookings.orders.store', $this->booking), [
        'service_id' => $lunch->id,
        'quantity' => 1,
        'billing_type' => 'charged_to_room',
        'notes' => 'Klaim Inklusi: Makan Siang (Lunch)',
    ]);
    $lunchResponse->assertSessionHasNoErrors();

    $lunchItem = BookingItem::where('booking_id', $this->booking->id)
        ->where('service_id', $lunch->id)
        ->first();
    $this->assertNotNull($lunchItem);
    $this->assertTrue((bool)$lunchItem->is_free_inclusion);
    $this->assertEquals(0.00, (float)$lunchItem->subtotal);
});

