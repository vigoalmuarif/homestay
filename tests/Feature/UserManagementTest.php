<?php

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(UserSeeder::class);
});

test('guest is redirected to login', function () {
    $response = $this->get('/users');
    $response->assertRedirect('/login');
});

test('receptionist cannot access user management', function () {
    $receptionist = User::where('email', 'receptionist@homestay.test')->first();

    $response = $this->actingAs($receptionist)->get('/users');
    $response->assertStatus(403);
});

test('housekeeping cannot access user management', function () {
    $housekeeping = User::where('email', 'housekeeping@homestay.test')->first();

    $response = $this->actingAs($housekeeping)->get('/users');
    $response->assertStatus(403);
});

test('admin can access user management and see users list', function () {
    $admin = User::where('email', 'admin@homestay.test')->first();

    $response = $this->actingAs($admin)->get('/users');
    $response->assertStatus(200);
    $response->assertSee('Kelola Petugas');
    $response->assertSee('admin@homestay.test');
    $response->assertSee('receptionist@homestay.test');
});

test('admin can create new user with role', function () {
    $admin = User::where('email', 'admin@homestay.test')->first();

    $response = $this->actingAs($admin)->post('/users', [
        'name' => 'Budi Petugas Baru',
        'email' => 'budi.baru@homestay.test',
        'role' => 'receptionist',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseHas('users', [
        'name' => 'Budi Petugas Baru',
        'email' => 'budi.baru@homestay.test',
    ]);

    $newUser = User::where('email', 'budi.baru@homestay.test')->first();
    expect($newUser)->not->toBeNull();
    expect($newUser->hasRole('receptionist'))->toBeTrue();
});

test('non-admin cannot create new user', function () {
    $receptionist = User::where('email', 'receptionist@homestay.test')->first();

    $response = $this->actingAs($receptionist)->post('/users', [
        'name' => 'Hacker Petugas',
        'email' => 'hacker@test.com',
        'role' => 'admin',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('users', [
        'email' => 'hacker@test.com',
    ]);
});

test('admin can update user and their role', function () {
    $admin = User::where('email', 'admin@homestay.test')->first();
    $housekeeping = User::where('email', 'housekeeping@homestay.test')->first();

    $response = $this->actingAs($admin)->put("/users/{$housekeeping->id}", [
        'name' => 'Staf Dipromosikan',
        'email' => $housekeeping->email,
        'role' => 'receptionist',
    ]);

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseHas('users', [
        'id' => $housekeeping->id,
        'name' => 'Staf Dipromosikan',
    ]);

    $housekeeping->refresh();
    expect($housekeeping->hasRole('receptionist'))->toBeTrue();
    expect($housekeeping->hasRole('housekeeping'))->toBeFalse();
});

test('admin cannot delete own logged-in account', function () {
    $admin = User::where('email', 'admin@homestay.test')->first();

    $response = $this->actingAs($admin)->delete("/users/{$admin->id}");
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('admin cannot delete the last remaining admin', function () {
    $admin = User::where('email', 'admin@homestay.test')->first();
    $housekeeping = User::where('email', 'housekeeping@homestay.test')->first();

    // Make housekeeping an admin too so we have 2 admins
    $housekeeping->assignRole('admin');

    // Delete one admin -> success
    $response = $this->actingAs($admin)->delete("/users/{$housekeeping->id}");
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('users', ['id' => $housekeeping->id]);

    // Now try to delete the last admin -> forbidden by logic
    $response2 = $this->actingAs($admin)->delete("/users/{$admin->id}");
    $response2->assertSessionHas('error');
});
