<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ManagerReceptionistController;
use App\Http\Controllers\ReceptionistController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::prefix('dashboard')->group(function () {
    // Dashboard homepage route
    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    // Admin routes
    Route::middleware(['auth','role:admin'])->group(function(){
        Route::resource('managers', ManagerController::class);
        Route::resource('receptionists', ReceptionistController::class);
        Route::resource('clients', ClientController::class);

        Route::post('receptionists/{receptionist}/ban', [AdminUserController::class, 'ban'])->name('receptionists.ban');
        Route::post('receptionists/{receptionist}/unban', [AdminUserController::class, 'unban'])->name('receptionists.unban');
    });

    // Manager routes
    Route::middleware(['auth','role:manager'])->group(function(){
        Route::resource('receptionists', ReceptionistController::class);
        Route::resource('clients', ClientController::class);

        Route::post('receptionists/{receptionist}/ban', [ManagerReceptionistController::class, 'ban'])->name('receptionists.ban');
        Route::post('receptionists/{receptionist}/unban', [ManagerReceptionistController::class, 'unban'])->name('receptionists.unban');
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
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/floor.php';
require __DIR__.'/room.php';
require __DIR__.'/client.php';
