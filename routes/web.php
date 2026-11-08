<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\InclusionClaimController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomOrderController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\RoomTypeServiceInclusionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::redirect('/signin', '/login');
    Route::get('/signup', function () {
        return view('pages.auth.signup', ['title' => 'Sign Up']);
    })->name('signup');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard (All Staff Roles)
    Route::middleware(['permission:view dashboard'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);
    });

    Route::get('/ecommerce', function () {
        return view('pages.dashboard.ecommerce', ['title' => 'E-commerce Dashboard (Demo)']);
    })->name('ecommerce');

    // Calendar Okupansi
    Route::middleware(['permission:view bookings'])->group(function () {
        Route::get('/calendar', function () {
            return view('pages.calendar', ['title' => 'Calendar']);
        })->name('calendar');
    });

    // Booking & Front Desk Operations
    Route::middleware(['permission:view bookings'])->group(function () {
        Route::get('/bookings/available-rooms', [BookingController::class, 'availableRooms'])->name('bookings.available-rooms');
        Route::get('/bookings/calendar-events', [BookingController::class, 'calendarEvents'])->name('bookings.calendar-events');

        Route::middleware(['permission:checkin checkout'])->group(function () {
            Route::post('/bookings/{booking}/check-in', [BookingController::class, 'checkIn'])->name('bookings.check-in');
            Route::post('/bookings/{booking}/check-out', [BookingController::class, 'checkOut'])->name('bookings.check-out');
        });

        Route::middleware(['permission:manage bookings'])->group(function () {
            Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        });

        Route::middleware(['permission:manage room-orders'])->group(function () {
            Route::post('/bookings/{booking}/orders', [RoomOrderController::class, 'store'])->name('bookings.orders.store');
            Route::delete('/bookings/{booking}/orders/{bookingItem}', [RoomOrderController::class, 'destroy'])->name('bookings.orders.destroy');
        });

        Route::resource('bookings', BookingController::class);
    });

    // Customer Management
    Route::middleware(['permission:view customers'])->group(function () {
        Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');

        Route::middleware(['permission:manage customers'])->group(function () {
            Route::post('/customers/{customer}/toggle-blacklist', [CustomerController::class, 'toggleBlacklist'])->name('customers.toggle-blacklist');
        });

        Route::resource('customers', CustomerController::class);
    });

    // Room & Room Types
    Route::middleware(['permission:view rooms'])->group(function () {
        Route::post('/rooms/{room}/update-status', [RoomController::class, 'updateStatus'])
            ->middleware('permission:update room status')
            ->name('rooms.update-status');

        // Master room & room type catalog modification (Admin/Owner only)
        Route::middleware(['permission:manage rooms'])->group(function () {
            Route::resource('room-types', RoomTypeController::class)->except(['show', 'create', 'edit']);
            Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
            Route::match(['put', 'patch'], '/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
            Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
        });

        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    });

    // Services, F&B & Inclusions Catalog
    Route::middleware(['permission:view services'])->group(function () {
        Route::get('/services/search', [ServiceController::class, 'search'])->name('services.search');
        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');

        // Master Service Management & Package Inclusions (Admin only)
        Route::middleware(['permission:manage services'])->group(function () {
            Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
            Route::match(['put', 'patch'], '/services/{service}', [ServiceController::class, 'update'])->name('services.update');
            Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
            Route::post('/services/{service}/toggle-active', [ServiceController::class, 'toggleActive'])->name('services.toggle-active');

            Route::resource('service-inclusions', RoomTypeServiceInclusionController::class)
                ->parameters(['service-inclusions' => 'serviceInclusion'])
                ->only(['index', 'store', 'destroy']);
        });
    });

    // Daily Inclusions & Fulfillment Desk
    Route::middleware(['permission:view room-orders'])->prefix('inclusions')->name('inclusions.')->group(function () {
        Route::get('/claim-desk', [InclusionClaimController::class, 'index'])->name('claim-desk');

        Route::middleware(['permission:manage room-orders'])->group(function () {
            Route::post('/{booking}/quick-claim', [InclusionClaimController::class, 'quickClaim'])->name('quick-claim');
            Route::post('/{booking}/fulfillment-order', [InclusionClaimController::class, 'fulfillmentOrder'])->name('fulfillment-order');
            Route::patch('/items/{item}/status', [InclusionClaimController::class, 'updateFulfillmentStatus'])->name('update-status');
        });
    });

    // Housekeeping Operations
    Route::middleware(['permission:view housekeeping'])->group(function () {
        Route::get('/housekeeping', [HousekeepingController::class, 'index'])->name('housekeeping.index');
        Route::post('/housekeeping/rooms/{room}/status', [HousekeepingController::class, 'updateRoomStatus'])
            ->middleware('permission:update room status')
            ->name('housekeeping.rooms.status');

        Route::middleware(['permission:manage housekeeping'])->group(function () {
            Route::post('/housekeeping/tasks', [HousekeepingController::class, 'storeTask'])->name('housekeeping.tasks.store');
            Route::patch('/housekeeping/tasks/{task}/status', [HousekeepingController::class, 'updateTaskStatus'])->name('housekeeping.tasks.update-status');
            Route::delete('/housekeeping/tasks/{task}', [HousekeepingController::class, 'destroyTask'])->name('housekeeping.tasks.destroy');
        });
    });

    // Folio Billing, Settlement & Financial Invoicing
    Route::middleware(['permission:view billing'])->prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::get('/export-csv', [BillingController::class, 'exportCsv'])->name('export-csv');
        Route::get('/{booking}', [BillingController::class, 'show'])->name('show');
        Route::get('/{booking}/invoice', [BillingController::class, 'invoice'])->name('invoice');

        Route::middleware(['permission:process payments'])->group(function () {
            Route::post('/{booking}/settle', [BillingController::class, 'settle'])->name('settle');
        });
    });

    // Financial & Operational Reports (Admin / Manager only)
    Route::middleware(['permission:view reports'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export-csv', [ReportController::class, 'exportCsv'])->name('export-csv');
    });

    // Petugas & Role Management (Sprint Sprint / Admin Only)
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
    });

    // profile pages
    Route::get('/profile', function () {
        return view('pages.profile', ['title' => 'Profile']);
    })->name('profile');

    // form pages
    Route::get('/form-elements', function () {
        return view('pages.forms.form-elements', ['title' => 'Form Elements']);
    })->name('form-elements');

    // tables pages
    Route::get('/basic-tables', function () {
        return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
    })->name('basic-tables');

    // pages
    Route::get('/blank', function () {
        return view('pages.pages.blank', ['title' => 'Blank']);
    })->name('blank');

    // error pages
    Route::get('/error-404', function () {
        return view('pages.errors.error-404', ['title' => 'Error 404']);
    })->name('error-404');

    // chart pages
    Route::get('/line-chart', function () {
        return view('pages.chart.line-chart', ['title' => 'Line Chart']);
    })->name('line-chart');

    Route::get('/bar-chart', function () {
        return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
    })->name('bar-chart');

    // ui elements pages
    Route::get('/alerts', function () {
        return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
    })->name('alerts');

    Route::get('/avatars', function () {
        return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
    })->name('avatars');

    Route::get('/badge', function () {
        return view('pages.ui-elements.badge', ['title' => 'Badges']);
    })->name('badge');

    Route::get('/buttons', function () {
        return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
    })->name('buttons');

    Route::get('/images', function () {
        return view('pages.ui-elements.images', ['title' => 'Images']);
    })->name('images');

    Route::get('/videos', function () {
        return view('pages.ui-elements.videos', ['title' => 'Videos']);
    })->name('videos');

    // legacy URLs
    Route::redirect('/badges', '/badge');
});
