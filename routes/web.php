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

// Test UI Routes
Route::prefix('manage')->group(function () {
    Route::get('managers', fn() => Inertia::render('Admin/ManageManagers'))->name('manage.managers');
    Route::get('receptionists', fn() => Inertia::render('Admin/ManageReceptionists'))->name('manage.receptionists');
    Route::get('clients', fn() => Inertia::render('Admin/ManageClients'))->name('manage.clients');
});

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