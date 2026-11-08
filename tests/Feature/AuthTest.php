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

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSee('Sign In');
    $response->assertSee('admin@homestay.test');
});

test('super admin can authenticate and redirect to dashboard', function () {
    $response = $this->post('/login', [
        'email' => 'admin@homestay.test',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));
});

test('receptionist can authenticate and redirect to dashboard', function () {
    $response = $this->post('/login', [
        'email' => 'receptionist@homestay.test',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));
});

test('users cannot authenticate with invalid password', function () {
    $response = $this->post('/login', [
        'email' => 'admin@homestay.test',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('unauthenticated users are redirected to login', function () {
    $response = $this->get('/ecommerce');

    $response->assertRedirect(route('login'));
});

test('authenticated user can access dashboard', function () {
    $user = User::where('email', 'admin@homestay.test')->first();

    $response = $this->actingAs($user)->get('/ecommerce');

    $response->assertStatus(200);
});

test('users can log out', function () {
    $user = User::where('email', 'admin@homestay.test')->first();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});
