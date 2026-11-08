<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
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

test('unauthenticated users are redirected from booking pages', function () {
    $response = $this->get(route('bookings.index'));
    $response->assertRedirect(route('login'));

    $response = $this->get(route('bookings.create'));
    $response->assertRedirect(route('login'));
});

test('authenticated staff can view bookings index with metrics', function () {
    $response = $this->actingAs($this->receptionist)->get(route('bookings.index'));

    $response->assertStatus(200);
    $response->assertSee('Reservasi &amp; Front Desk (Tamu Aktif)', false);
    $response->assertSee('Tamu In-House (Menginap)');
    $response->assertSee('Kedatangan Hari Ini');
    $response->assertSee('Kepulangan Hari Ini');
});

test('staff can view booking creation form', function () {
    $response = $this->actingAs($this->receptionist)->get(route('bookings.create'));

    $response->assertStatus(200);
    $response->assertSee('Buat Reservasi Baru');
    $response->assertSee('1. Data Tamu / Pemesan');
    $response->assertSee('2. Periode &amp; Pilihan Kamar', false);
});

test('booking can be successfully created with correct night calculation', function () {
    $customer = Customer::where('is_blacklisted', false)->first();
    // find an available room for next month
    $room = Room::first();

    $inDate = Carbon::today()->addMonths(2)->format('Y-m-d');
    $outDate = Carbon::today()->addMonths(2)->addDays(3)->format('Y-m-d');

    $response = $this->actingAs($this->receptionist)->post(route('bookings.store'), [
        'customer_id' => $customer->id,
        'room_id' => $room->id,
        'check_in_date' => $inDate,
        'check_out_date' => $outDate,
        'room_price_snapshot' => 350000,
        'deposit_amount' => 150000,
        'status' => 'confirmed',
        'payment_status' => 'partial',
        'notes' => 'Early check in requested',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('bookings', [
        'customer_id' => $customer->id,
        'room_id' => $room->id,
        'total_nights' => 3,
        'status' => 'confirmed',
    ]);
    $created = Booking::where('customer_id', $customer->id)->where('total_nights', 3)->first();
    $this->assertNotNull($created);
    $this->assertEquals($inDate, $created->check_in_date->format('Y-m-d'));
    $this->assertEquals($outDate, $created->check_out_date->format('Y-m-d'));
});

test('overbooking conflict is strictly prevented on same room and overlapping dates', function () {
    $customer = Customer::where('is_blacklisted', false)->first();
    $room = Room::first();

    $inDate = Carbon::today()->addMonths(3)->format('Y-m-d');
    $outDate = Carbon::today()->addMonths(3)->addDays(4)->format('Y-m-d');

    // Create first booking
    Booking::create([
        'booking_code' => 'BK-TEST-EXISTING',
        'customer_id' => $customer->id,
        'room_id' => $room->id,
        'check_in_date' => $inDate,
        'check_out_date' => $outDate,
        'total_nights' => 4,
        'room_price_snapshot' => 350000,
        'status' => 'confirmed',
    ]);

    // Attempt to book overlapping dates on same room (e.g. 1 day after inDate)
    $overlapIn = Carbon::parse($inDate)->addDay()->format('Y-m-d');
    $overlapOut = Carbon::parse($outDate)->addDay()->format('Y-m-d');

    $response = $this->actingAs($this->receptionist)->post(route('bookings.store'), [
        'customer_id' => $customer->id,
        'room_id' => $room->id,
        'check_in_date' => $overlapIn,
        'check_out_date' => $overlapOut,
        'room_price_snapshot' => 350000,
    ]);

    $response->assertSessionHasErrors(['room_id']);
});

test('blacklisted customer cannot be booked', function () {
    $blacklistedCustomer = Customer::where('is_blacklisted', true)->first();
    $this->assertNotNull($blacklistedCustomer);

    $room = Room::first();
    $inDate = Carbon::today()->addMonths(4)->format('Y-m-d');
    $outDate = Carbon::today()->addMonths(4)->addDays(2)->format('Y-m-d');

    $response = $this->actingAs($this->receptionist)->post(route('bookings.store'), [
        'customer_id' => $blacklistedCustomer->id,
        'room_id' => $room->id,
        'check_in_date' => $inDate,
        'check_out_date' => $outDate,
        'room_price_snapshot' => 350000,
    ]);

    $response->assertSessionHasErrors(['customer_id']);
});

test('check out date must be after check in date', function () {
    $customer = Customer::where('is_blacklisted', false)->first();
    $room = Room::first();

    $response = $this->actingAs($this->receptionist)->post(route('bookings.store'), [
        'customer_id' => $customer->id,
        'room_id' => $room->id,
        'check_in_date' => '2026-12-10',
        'check_out_date' => '2026-12-10',
        'room_price_snapshot' => 350000,
    ]);

    $response->assertSessionHasErrors(['check_out_date']);
});

test('fast check-in marks booking as checked_in and room as occupied', function () {
    $booking = Booking::where('status', 'confirmed')->first();
    $this->assertNotNull($booking);

    $response = $this->actingAs($this->receptionist)->post(route('bookings.check-in', $booking), [
        'deposit_amount' => 250000,
    ]);

    $response->assertSessionHas('success');
    $this->assertEquals('checked_in', $booking->fresh()->status);
    $this->assertNotNull($booking->fresh()->actual_check_in_at);
    $this->assertEquals('occupied', $booking->fresh()->room->status);
    $this->assertEquals(250000.00, (float)$booking->fresh()->deposit_amount);
});

test('fast check-out marks booking as checked_out and room as dirty', function () {
    $booking = Booking::where('status', 'checked_in')->first();
    $this->assertNotNull($booking);

    $response = $this->actingAs($this->receptionist)->post(route('bookings.check-out', $booking));

    $response->assertSessionHas('success');
    $this->assertEquals('checked_out', $booking->fresh()->status);
    $this->assertNotNull($booking->fresh()->actual_check_out_at);
    $this->assertEquals('dirty', $booking->fresh()->room->status);
});

test('cancelling booking restores room status if occupied', function () {
    $booking = Booking::where('status', 'checked_in')->first();
    $this->assertNotNull($booking);

    $response = $this->actingAs($this->receptionist)->post(route('bookings.cancel', $booking));

    $response->assertSessionHas('success');
    $this->assertEquals('cancelled', $booking->fresh()->status);
    $this->assertEquals('available', $booking->fresh()->room->status);
});

test('available rooms API returns available rooms for dates', function () {
    $inDate = Carbon::today()->addMonths(5)->format('Y-m-d');
    $outDate = Carbon::today()->addMonths(5)->addDays(2)->format('Y-m-d');

    $response = $this->actingAs($this->receptionist)->getJson(route('bookings.available-rooms', [
        'check_in' => $inDate,
        'check_out' => $outDate,
    ]));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        '*' => ['id', 'room_number', 'room_type_id', 'room_type_name', 'base_price', 'floor']
    ]);
});

test('calendar events API returns formatted events for occupancy visualization', function () {
    $response = $this->actingAs($this->receptionist)->getJson(route('bookings.calendar-events'));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        '*' => ['id', 'title', 'start', 'end', 'url', 'extendedProps']
    ]);
});
