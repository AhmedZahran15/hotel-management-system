<?php

use App\Http\Controllers\RoomController;
use App\Http\Middleware\CheckForAnyPermission;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth'])->group(function () {

    Route::resource("/rooms", RoomController::class) ->only("store","create")->
    middleware([CheckForAnyPermission::class.':create rooms,manage rooms']);

    Route::resource("/rooms", RoomController::class) ->only("index","show")->
    middleware([CheckForAnyPermission::class.':view rooms,manage rooms']);

    Route::resource("/rooms", RoomController::class) ->only("edit","update")->
    middleware([CheckForAnyPermission::class.':edit rooms,manage rooms']);

    Route::resource("/rooms", RoomController::class) ->only("destroy")->
    middleware([CheckForAnyPermission::class.':delete rooms,manage rooms']);

});


