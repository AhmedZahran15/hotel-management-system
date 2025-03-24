<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ManagerReceptionistController;
use App\Http\Controllers\ReceptionistController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Middleware\CheckClientApproval;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

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


// Include other route files
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/floor.php';
require __DIR__ . '/room.php';
require __DIR__ . '/manager.php';
require __DIR__ . '/shared.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/client.php';