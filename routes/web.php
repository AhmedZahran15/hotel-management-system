<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->prefix('dashboard')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// Public Routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

<<<<<<< HEAD
// Protected routes
Route::middleware(['auth', 'verified', CheckClientApproval::class])->group(function () {
    Route::prefix('dashboard')->group(function () {
        // Dashboard homepage
        Route::get('/', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');

        // Client-specific routes (most specific reservation routes first)
        Route::middleware(['role:client'])->group(function () {
            Route::prefix('reservations')->group(function () {
                // Payment routes
                Route::prefix('payment')->name('reservations.payment.')->group(function () {
                    Route::get('success', [ReservationController::class, 'handlePaymentSuccess'])
                        ->name('success');
                    Route::get('cancel', [ReservationController::class, 'handlePaymentCancel'])
                        ->name('cancel');
                });
                
                // Store route
                Route::post('/', [ReservationController::class, 'store'])
                    ->name('reservations.store');
            });
        });

    });
});

=======
// Test UI Routes
Route::prefix('manage')->group(function () {
    Route::get('managers', fn() => Inertia::render('Admin/ManageManagers'))->name('manage.managers');
    Route::get('receptionists', fn() => Inertia::render('Admin/ManageReceptionists'))->name('manage.receptionists');
    Route::get('clients', fn() => Inertia::render('Admin/ManageClients'))->name('manage.clients');
});
>>>>>>> 7fa6cf1b7a1f54f3d3279164aa6172f458428330

// Include Additional Route Files
require __DIR__ . '/admin.php';
require __DIR__ . '/manager.php';
require __DIR__ . '/client.php';
require __DIR__ . '/shared.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/floor.php';
require __DIR__ . '/room.php';
require __DIR__ . '/reservation.php';