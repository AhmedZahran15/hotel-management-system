<?php

use App\Http\Controllers\FloorController;
use App\Http\Middleware\CheckForAnyPermission;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix("dashboard") ->group(function () {

    Route::resource('floors',FloorController::class)
    ->only('craete','store')->middleware([CheckForAnyPermission::class . ':create floors,manage floors']);

    Route::resource('floors',FloorController::class)
    ->only('index','show')->middleware([CheckForAnyPermission::class . ':view floors,manage floors']);

    Route::resource('floors',FloorController::class)
    ->only('edit','update')->middleware([CheckForAnyPermission::class . ':edit floors,manage floors']);

    Route::resource('floors',FloorController::class)
    ->only('destroy')->middleware([CheckForAnyPermission::class . ':delete floors,manage floors']);

});


