<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\AdminUserController;

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('dashboard')->group(function () {
    Route::resource('managers', ManagerController::class);
    
    Route::prefix('receptionists')->name('admin.receptionists.')->group(function () {
        Route::post('{receptionist}/ban', [AdminUserController::class, 'ban'])->name('ban');
        Route::post('{receptionist}/unban', [AdminUserController::class, 'unban'])->name('unban');
    });
});
