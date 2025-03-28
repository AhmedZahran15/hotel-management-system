<?php

use App\Http\Controllers\ClientExportController;
use App\Http\Controllers\ManagerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->prefix('dashboard')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('managers', ManagerController::class);
    });
    Route::middleware(['role:manager'])->prefix('manager')->group(function () {
        Route::prefix('clients')->name('manager.clients.')->group(function () {
            Route::get('export', [ClientExportController::class, 'export'])->name('export');});
        });
});

// Public Routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Include Additional Route Files
require __DIR__ . '/client.php';
require __DIR__ . '/shared.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/floor.php';
require __DIR__ . '/room.php';
require __DIR__ . '/reservation.php';