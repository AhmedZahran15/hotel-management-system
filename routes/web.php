<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ManagerReceptionistController;
use App\Http\Controllers\ReceptionistController;
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

        // Shared routes for admin and manager
        Route::middleware(['role:admin|manager'])->group(function () {
            // Basic receptionist management
            Route::resource('receptionists', ReceptionistController::class);
        });

        // Admin-specific routes
        Route::middleware(['role:admin'])->group(function () {
            // Manager management
            Route::resource('managers', ManagerController::class);
            
            // Client management
            Route::resource('clients', ClientController::class);
            
            // Admin specific receptionist actions
            Route::prefix('receptionists')->name('admin.receptionists.')->group(function () {
                Route::post('{receptionist}/ban', [AdminUserController::class, 'ban'])->name('ban');
                Route::post('{receptionist}/unban', [AdminUserController::class, 'unban'])->name('unban');
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
    });
});

// Test UI routes
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
