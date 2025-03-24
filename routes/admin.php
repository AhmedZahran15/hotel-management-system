<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AdminUserController;

Route::prefix('dashboard')->middleware(['auth','role:admin'])->group(function () {
    // Manager management
    Route::resource('managers', ManagerController::class);
    
    // Admin-specific receptionist actions
    Route::prefix('receptionists')->name('admin.receptionists.')->group(function () {
        Route::post('{receptionist}/ban', [AdminUserController::class, 'ban'])
            ->name('ban');
        Route::post('{receptionist}/unban', [AdminUserController::class, 'unban'])
            ->name('unban');
    });
});
