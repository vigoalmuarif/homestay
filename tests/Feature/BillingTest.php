<?php

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Customer;
use App\Models\Room;
use App\Models\Service;
use App\Models\User;
use App\Services\FolioCalculationService;
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

test('unauthenticated users are redirected from billing pages', function () {
    $response = $this->get(route('billing.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated staff can view billing index with financial metrics', function () {
    $response = $this->actingAs($this->receptionist)->get(route('billing.index'));

    $response->assertStatus(200);
    $response->assertSee('Folio Billing &amp; Settlement', false);
    $response->assertSee('Total Omzet (Bruto)');
    $response->assertSee('Piutang / Sisa Tagihan');
    $response->assertSee('Uang Jaminan / Deposit');
});

test('folio calculation service accurately computes room charges and balances', function () {
    $service = app(FolioCalculationService::class);

    $customer = Customer::first();
    $room = Room::first();

    $booking = Booking::create([
        'booking_code' => Booking::generateCode(),
        'customer_id' => $customer->id,
        'room_id' => $room->id,
        'check_in_date' => Carbon::now(),
        'check_out_date' => Carbon::now()->addDays(2),
        'total_nights' => 2,
        'room_price_snapshot' => 350000.00,
        'deposit_amount' => 100000.00,
        'status' => 'checked_in',
        'payment_status' => 'partial',
    ]);

    $serviceItem = Service::first();

    // Add extra order charged to room
    BookingItem::create([
        'booking_id' => $booking->id,
        'service_id' => $serviceItem->id,
        'item_name' => 'Nasi Goreng Spesial',
        'quantity' => 2,
        'unit_price' => 30000.00,
        'discount_or_allowance' => 0.00,
        'subtotal' => 60000.00,
        'billing_type' => 'charged_to_room',
        'status' => 'delivered',
        'is_free_inclusion' => false,
    ]);

    // Add free inclusion item
    BookingItem::create([
        'booking_id' => $booking->id,
        'service_id' => $serviceItem->id,
        'item_name' => 'Sarapan Pagi Gratis',
        'quantity' => 2,
        'unit_price' => 30000.00,
        'discount_or_allowance' => 60000.00,
        'subtotal' => 0.00,
        'billing_type' => 'charged_to_room',
        'status' => 'delivered',
        'is_free_inclusion' => true,
    ]);

    $folio = $service->calculateFolio($booking);

    expect($folio['total_nights'])->toBe(2);
    expect($folio['total_room_charge'])->toBe(700000.00);
    expect($folio['total_charged_to_room'])->toBe(60000.00);
    expect($folio['gross_total'])->toBe(760000.00);
    expect($folio['deposit_amount'])->toBe(100000.00);
    expect($folio['balance_due'])->toBe(660000.00);
    expect($folio['is_settled'])->toBeFalse();
});

test('staff can view single booking folio statement and settlement view', function () {
    $booking = Booking::first();

    $response = $this->actingAs($this->receptionist)->get(route('billing.show', $booking));

    $response->assertStatus(200);
    $response->assertSee($booking->booking_code);
    $response->assertSee('Rincian Item Tagihan Folio');
    $response->assertSee('Kalkulasi Tagihan Folio');
    $response->assertSee('Proses Kasir / Settlement');
});

test('staff can settle folio balance and auto checkout guest with dirty room status', function () {
    $customer = Customer::first();
    $room = Room::first();
    $room->update(['status' => 'occupied']);

    $booking = Booking::create([
        'booking_code' => Booking::generateCode(),
        'customer_id' => $customer->id,
        'room_id' => $room->id,
        'check_in_date' => Carbon::now()->subDays(1),
        'check_out_date' => Carbon::now(),
        'total_nights' => 1,
        'room_price_snapshot' => 300000.00,
        'deposit_amount' => 50000.00,
        'status' => 'checked_in',
        'payment_status' => 'partial',
    ]);

    $response = $this->actingAs($this->receptionist)->post(route('billing.settle', $booking), [
        'amount_paid' => 250000.00,
        'payment_method' => 'cash',
        'notes' => 'Pelunasan tunai kasir saat check out',
        'auto_checkout' => 1,
    ]);

    $response->assertSessionHas('success');
    $booking->refresh();
    $room->refresh();

    expect($booking->payment_status)->toBe('paid');
    expect($booking->status)->toBe('checked_out');
    expect($room->status)->toBe('dirty');
    expect($booking->notes)->toContain('Pelunasan tunai kasir saat check out');
});

test('staff can view printable guest invoice', function () {
    $booking = Booking::first();

    $response = $this->actingAs($this->receptionist)->get(route('billing.invoice', $booking));

    $response->assertStatus(200);
    $response->assertSee($booking->booking_code);
    $response->assertSee('TailAdmin Homestay');
    $response->assertSee('FAKTUR / INVOICE RESMI');
});

test('staff can export billing records as csv', function () {
    $response = $this->actingAs($this->receptionist)->get(route('billing.export-csv'));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

