<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ReceptionistController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

//profile route for all authenticated users 
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

//acceciable routes for admin only
Route::middleware(['auth','role:admin'])->group(function(){
    Route::resource('managers', ManagerController::class);
    Route::resource('receptionists', ReceptionistController::class);
    Route::resource('clients', ClientController::class);
});
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
