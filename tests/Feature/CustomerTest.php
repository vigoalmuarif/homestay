<?php

use App\Models\Customer;
use App\Models\User;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(UserSeeder::class);
    $this->seed(CustomerSeeder::class);

    $this->admin = User::where('email', 'admin@homestay.test')->first();
});

test('authenticated user can view customer list', function () {
    $response = $this->actingAs($this->admin)->get(route('customers.index'));

    $response->assertStatus(200);
    $response->assertSee('Data Tamu / Customer');
    $response->assertSee('Hendra Pratama');
    $response->assertSee('Sarah Wijaya');
    $response->assertSee('Agus Permana');
});

test('customer search filters results by keyword', function () {
    $response = $this->actingAs($this->admin)->get(route('customers.index', ['search' => 'Hendra']));

    $response->assertStatus(200);
    $response->assertSee('Hendra Pratama');
    $response->assertDontSee('Sarah Wijaya');
});

test('customer filter by blacklisted status shows only blacklisted guests', function () {
    $response = $this->actingAs($this->admin)->get(route('customers.index', ['status' => 'blacklisted']));

    $response->assertStatus(200);
    $response->assertSee('Agus Permana');
    $response->assertDontSee('Hendra Pratama');
});

test('customer search API returns json results for reservation autocomplete', function () {
    $response = $this->actingAs($this->admin)->getJson(route('customers.search', ['q' => '081234567890']));

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'full_name' => 'Hendra Pratama',
        'phone_number' => '081234567890',
        'is_blacklisted' => false,
    ]);
});

test('new customer can be created with valid data', function () {
    $response = $this->actingAs($this->admin)->post(route('customers.store'), [
        'full_name' => 'Jessica Wong',
        'identity_type' => 'paspor',
        'identity_number' => 'P12345678',
        'phone_number' => '089911223344',
        'email' => 'jessica@example.com',
        'address' => 'Sydney, Australia',
        'country' => 'Australia',
        'special_notes' => 'Early check-in request',
        'is_blacklisted' => 0,
    ]);

    $response->assertRedirect(route('customers.index'));
    $this->assertDatabaseHas('customers', [
        'full_name' => 'Jessica Wong',
        'identity_number' => 'P12345678',
    ]);
});

test('new customer can be created via ajax returning json', function () {
    $response = $this->actingAs($this->admin)->postJson(route('customers.store'), [
        'full_name' => 'Modal Quick Guest',
        'identity_type' => 'ktp',
        'identity_number' => '3201999988880001',
        'phone_number' => '085566778899',
        'email' => 'modal@example.com',
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'success' => true,
        'data' => [
            'full_name' => 'Modal Quick Guest',
        ],
    ]);
});

test('customer creation validates duplicate identity number', function () {
    $existing = Customer::first();

    $response = $this->actingAs($this->admin)->post(route('customers.store'), [
        'full_name' => 'Duplicate Guest',
        'identity_type' => 'ktp',
        'identity_number' => $existing->identity_number,
        'phone_number' => '081111222233',
    ]);

    $response->assertSessionHasErrors('identity_number');
});

test('customer detail page can be viewed', function () {
    $customer = Customer::where('full_name', 'Hendra Pratama')->first();

    $response = $this->actingAs($this->admin)->get(route('customers.show', $customer));

    $response->assertStatus(200);
    $response->assertSee('Hendra Pratama');
    $response->assertSee($customer->phone_number);
});

test('customer blacklist status can be toggled', function () {
    $customer = Customer::where('full_name', 'Hendra Pratama')->first();
    $this->assertFalse($customer->is_blacklisted);

    $response = $this->actingAs($this->admin)->post(route('customers.toggle-blacklist', $customer));

    $response->assertRedirect();
    $this->assertTrue($customer->fresh()->is_blacklisted);
});

test('customer can be updated', function () {
    $customer = Customer::where('full_name', 'Hendra Pratama')->first();

    $response = $this->actingAs($this->admin)->put(route('customers.update', $customer), [
        'full_name' => 'Hendra Pratama, S.T.',
        'identity_type' => 'ktp',
        'identity_number' => $customer->identity_number,
        'phone_number' => '081234567890',
        'email' => 'hendra.updated@gmail.com',
    ]);

    $response->assertRedirect(route('customers.index'));
    $this->assertEquals('Hendra Pratama, S.T.', $customer->fresh()->full_name);
});

test('customer can be deleted', function () {
    $customer = Customer::where('full_name', 'Budi Santoso')->first();

    $response = $this->actingAs($this->admin)->delete(route('customers.destroy', $customer));

    $response->assertRedirect(route('customers.index'));
    $this->assertDatabaseMissing('customers', [
        'id' => $customer->id,
    ]);
});

