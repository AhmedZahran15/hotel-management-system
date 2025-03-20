<?php

use App\Http\Controllers\RoomController;
use App\Http\Middleware\CheckForAnyPermission;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::middleware(CheckForAnyPermission::class.'create rooms,manage rooms')->group(function () {
        Route::get('/rooms/create', [RoomController::class, 'create']);
        Route::post('/rooms', [RoomController::class, 'store']);
    });

    Route::middleware(CheckForAnyPermission::class.'view rooms,manage rooms')->group(function () {
        Route::get('/rooms', [RoomController::class, 'index']);
        Route::get('/rooms/{floor}', [RoomController::class, 'show']);
    });

    Route::middleware(CheckForAnyPermission::class.'edit rooms,manage rooms')->group(function () {
        Route::get('/rooms/{floor}/edit', [RoomController::class, 'edit']);
        Route::put('/rooms/{floor}', [RoomController::class, 'update']);
    });

    Route::middleware(CheckForAnyPermission::class.'delete rooms,manage rooms')->group(function () {
        Route::delete('/rooms/{floor}', [RoomController::class, 'destroy']);
    });
});


