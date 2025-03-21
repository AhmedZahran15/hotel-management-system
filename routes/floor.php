<?php

use App\Http\Controllers\FloorController;
use App\Http\Middleware\CheckForAnyPermission;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::middleware([CheckForAnyPermission::class . ':create floors,manage floors'])->group(function () {
        Route::get('/floors/create', [FloorController::class, 'create']);
        Route::post('/floors', [FloorController::class, 'store']);
    });

    Route::middleware([CheckForAnyPermission::class . ':view floors,manage floors'])->group(function () {
        Route::get('/floors', [FloorController::class, 'index']);
        Route::get('/floors/{floor}', [FloorController::class, 'show']);
    });

    Route::middleware([CheckForAnyPermission::class.':edit floors,manage floors'])->group(function () {
        Route::get('/floors/{floor}/edit', [FloorController::class, 'edit']);
        Route::put('/floors/{floor}', [FloorController::class, 'update']);
    });

    Route::middleware([CheckForAnyPermission::class.':delete floors,manage floors'])->group(function () {
        Route::delete('/floors/{floor}', [FloorController::class, 'destroy']);
    });
});


