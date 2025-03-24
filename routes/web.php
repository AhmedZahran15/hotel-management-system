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

        // Manager-specific routes
        Route::middleware(['role:manager'])->group(function () {
            // Client management
            Route::resource('clients', ClientController::class);

            // Manager specific receptionist actions
            Route::prefix('receptionists')->name('manager.receptionists.')->group(function () {
                Route::post('{receptionist}/ban', [ManagerReceptionistController::class, 'ban'])->name('ban');
                Route::post('{receptionist}/unban', [ManagerReceptionistController::class, 'unban'])->name('unban');
            });
        });

        // Shared routes for admin, manager and client (most general routes last)
        Route::middleware(['role:admin|manager|client'])->group(function () {
            // Available rooms route (specific before resource)
            Route::get('reservations/available', [ReservationController::class, 'availableRooms'])
                ->name('reservations.available');
            Route::get('reservations/rooms/{roomId}', [ReservationController::class, 'create'])
                ->name('reservations.create');
            
            // General resources
            Route::resource('receptionists', ReceptionistController::class);
            Route::resource('reservations', ReservationController::class)
                ->except(['store']); // Exclude store as it's handled in client routes
        });
    });
});

// Test UI routes (keep at the end as they're for testing)
Route::prefix('manage')->group(function () {
    Route::get('managers', function () {
        return Inertia::render('Admin/ManageManagers');
    });
    Route::get('receptionists', function () {
        return Inertia::render('Admin/ManageReceptionists');
    });
    Route::get('clients', function () {
        return Inertia::render('Admin/ManageClients');
    });
});

// Include other route files
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/floor.php';
require __DIR__ . '/room.php';
require __DIR__ . '/client.php';
require __DIR__ . '/admin.php';