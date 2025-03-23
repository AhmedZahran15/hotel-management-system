<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ManagerReceptionistController;
use App\Http\Controllers\ReceptionistController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Middleware\CheckClientApproval;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified',CheckClientApproval::class])->group(function () {
    Route::prefix('dashboard')->group(function () {
        // Dashboard homepage route
        Route::get('/', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');

        // Admin routes
        Route::middleware(['role:admin'])->prefix('admin')->group(function () {
            Route::resources([
                'managers' => ManagerController::class,
                'receptionists' => ReceptionistController::class,
                'clients' => ClientController::class,
            ]);

            Route::post('receptionists/{receptionist}/ban', [AdminUserController::class, 'ban'])->name('admin.receptionists.ban');
            Route::post('receptionists/{receptionist}/unban', [AdminUserController::class, 'unban'])->name('admin.receptionists.unban');
        });

        // Manager routes
        Route::middleware(['role:manager'])->prefix('manager')->group(function () {
            Route::resources([
                'receptionists' => ReceptionistController::class,
                'clients' => ClientController::class,
            ]);

            Route::post('receptionists/{receptionist}/ban', [ManagerReceptionistController::class, 'ban'])->name('manager.receptionists.ban');
            Route::post('receptionists/{receptionist}/unban', [ManagerReceptionistController::class, 'unban'])->name('manager.receptionists.unban');
        });

    });
});

// Test UI routes
Route::get('/manage-managers', function () {
    return Inertia::render('Admin/ManageManagers');
});

Route::get('/manage-receptionists', function () {
    return Inertia::render('Admin/ManageReceptionists');
});

Route::get('/manage-clients', function () {
    return Inertia::render('Admin/ManageClients');
});
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/floor.php';
require __DIR__ . '/room.php';
require __DIR__ . '/client.php';
