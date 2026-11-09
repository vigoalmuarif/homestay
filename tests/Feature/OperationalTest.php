<?php

use App\Models\OperationalTransaction;
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
    $this->housekeeping = User::where('email', 'housekeeping@homestay.test')->first();
});

test('unauthenticated users are redirected from operational pages', function () {
    $this->get(route('operational.index'))->assertRedirect(route('login'));
    $this->get(route('operational.incomes'))->assertRedirect(route('login'));
    $this->get(route('operational.expenses'))->assertRedirect(route('login'));
});

test('admin can access operational incomes and expenses pages', function () {
    $this->actingAs($this->admin);

    $responseIncome = $this->get(route('operational.incomes'));
    $responseIncome->assertStatus(200);
    $responseIncome->assertSee('Pemasukan Umum');

    $responseExpense = $this->get(route('operational.expenses'));
    $responseExpense->assertStatus(200);
    $responseExpense->assertSee('Pengeluaran Umum');
});

test('frontdesk (receptionist) can access operational incomes and expenses pages', function () {
    $this->actingAs($this->receptionist);

    $responseIncome = $this->get(route('operational.incomes'));
    $responseIncome->assertStatus(200);
    $responseIncome->assertSee('Pemasukan Umum');
    $responseIncome->assertSee('Tambah Pemasukan Umum');

    $responseExpense = $this->get(route('operational.expenses'));
    $responseExpense->assertStatus(200);
    $responseExpense->assertSee('Pengeluaran Umum');
    $responseExpense->assertSee('Tambah Pengeluaran Umum');
});

test('housekeeping can access operational incomes and expenses pages', function () {
    $this->actingAs($this->housekeeping);

    $responseIncome = $this->get(route('operational.incomes'));
    $responseIncome->assertStatus(200);
    $responseIncome->assertSee('Pemasukan Umum');
    $responseIncome->assertSee('Tambah Pemasukan Umum');

    $responseExpense = $this->get(route('operational.expenses'));
    $responseExpense->assertStatus(200);
    $responseExpense->assertSee('Pengeluaran Umum');
    $responseExpense->assertSee('Tambah Pengeluaran Umum');
});

test('frontdesk (receptionist) can add general income (pemasukan umum)', function () {
    $this->actingAs($this->receptionist);

    $payload = [
        'type' => 'income',
        'title' => 'Parkir Bus Wisatawan',
        'category' => 'Parkir Tamu / Kendaraan',
        'amount' => 50000,
        'transaction_date' => Carbon::today()->format('Y-m-d'),
        'payment_method' => 'cash',
        'notes' => 'Tamu grup check-in sore',
    ];

    $response = $this->post(route('operational.transactions.store'), $payload);

    $response->assertRedirect(route('operational.incomes'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('operational_transactions', [
        'type' => 'income',
        'title' => 'Parkir Bus Wisatawan',
        'amount' => 50000,
        'user_id' => $this->receptionist->id,
    ]);
});

test('frontdesk (receptionist) can add general expense (pengeluaran umum)', function () {
    $this->actingAs($this->receptionist);

    $payload = [
        'type' => 'expense',
        'title' => 'Isi Ulang Galon Aqua',
        'category' => 'Galon Air Minum & Gas',
        'amount' => 38000,
        'transaction_date' => Carbon::today()->format('Y-m-d'),
        'payment_method' => 'cash',
        'notes' => 'Untuk dispenser lobby tamu',
    ];

    $response = $this->post(route('operational.transactions.store'), $payload);

    $response->assertRedirect(route('operational.expenses'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('operational_transactions', [
        'type' => 'expense',
        'title' => 'Isi Ulang Galon Aqua',
        'amount' => 38000,
        'user_id' => $this->receptionist->id,
    ]);
});

test('housekeeping can add general income (pemasukan umum)', function () {
    $this->actingAs($this->housekeeping);

    $payload = [
        'type' => 'income',
        'title' => 'Penjualan Kardus Bekas Box Amenities',
        'category' => 'Penjualan Kardus / Barang Bekas',
        'amount' => 45000,
        'transaction_date' => Carbon::today()->format('Y-m-d'),
        'payment_method' => 'cash',
        'notes' => 'Barang daur ulang housekeeping',
    ];

    $response = $this->post(route('operational.transactions.store'), $payload);

    $response->assertRedirect(route('operational.incomes'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('operational_transactions', [
        'type' => 'income',
        'title' => 'Penjualan Kardus Bekas Box Amenities',
        'amount' => 45000,
        'user_id' => $this->housekeeping->id,
    ]);
});

test('housekeeping can add general expense (pengeluaran umum)', function () {
    $this->actingAs($this->housekeeping);

    $payload = [
        'type' => 'expense',
        'title' => 'Beli Pembersih Lantai & Spons',
        'category' => 'Bahan Pembersih & Deterjen',
        'amount' => 62000,
        'transaction_date' => Carbon::today()->format('Y-m-d'),
        'payment_method' => 'cash',
        'notes' => 'Stok kebersihan kamar lantai 1',
    ];

    $response = $this->post(route('operational.transactions.store'), $payload);

    $response->assertRedirect(route('operational.expenses'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('operational_transactions', [
        'type' => 'expense',
        'title' => 'Beli Pembersih Lantai & Spons',
        'amount' => 62000,
        'user_id' => $this->housekeeping->id,
    ]);
});

test('transaction validation requires mandatory fields and valid amount', function () {
    $this->actingAs($this->receptionist);

    $response = $this->post(route('operational.transactions.store'), [
        'type' => 'invalid_type',
        'title' => '',
        'category' => '',
        'amount' => -100,
        'transaction_date' => '',
        'payment_method' => '',
    ]);

    $response->assertSessionHasErrors(['type', 'title', 'category', 'amount', 'transaction_date', 'payment_method']);
});

test('user can update their own operational transaction', function () {
    $this->actingAs($this->housekeeping);

    $trx = OperationalTransaction::create([
        'type' => 'expense',
        'category' => 'Bahan Pembersih & Deterjen',
        'title' => 'Beli Karbol Wangi',
        'amount' => 20000,
        'transaction_date' => Carbon::today()->format('Y-m-d'),
        'payment_method' => 'cash',
        'user_id' => $this->housekeeping->id,
    ]);

    $response = $this->put(route('operational.transactions.update', $trx), [
        'category' => 'Bahan Pembersih & Deterjen',
        'title' => 'Beli Karbol Wangi & Sabun Pel',
        'amount' => 35000,
        'transaction_date' => Carbon::today()->format('Y-m-d'),
        'payment_method' => 'cash',
        'notes' => 'Tambahan 1 pouch sabun pel',
    ]);

    $response->assertRedirect(route('operational.expenses'));
    $trx->refresh();
    expect($trx->title)->toBe('Beli Karbol Wangi & Sabun Pel');
    expect((float) $trx->amount)->toEqual(35000.0);
});

test('user can delete their own operational transaction', function () {
    $this->actingAs($this->housekeeping);

    $trx = OperationalTransaction::create([
        'type' => 'income',
        'category' => 'Titip Bagasi / Penitipan',
        'title' => 'Titip Koper Salah Input',
        'amount' => 15000,
        'transaction_date' => Carbon::today()->format('Y-m-d'),
        'payment_method' => 'cash',
        'user_id' => $this->housekeeping->id,
    ]);

    $response = $this->delete(route('operational.transactions.destroy', $trx));

    $response->assertRedirect(route('operational.incomes'));
    $this->assertDatabaseMissing('operational_transactions', ['id' => $trx->id]);
});

test('staff can export operational transactions to csv', function () {
    $this->actingAs($this->receptionist);

    OperationalTransaction::create([
        'type' => 'income',
        'category' => 'Parkir Tamu / Kendaraan',
        'title' => 'Parkir Mobil Avanza',
        'amount' => 20000,
        'transaction_date' => Carbon::today()->format('Y-m-d'),
        'payment_method' => 'cash',
        'user_id' => $this->receptionist->id,
    ]);

    $response = $this->get(route('operational.export-csv', ['type' => 'income']));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

