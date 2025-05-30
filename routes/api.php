<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoomsController;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email'       => 'required|email',
        'password'    => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();
    
    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }
    
    return $user->createToken($request->device_name)->plainTextToken;
});

Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::delete('/clients/{id}', function ($id) {
        $client = Client::with('user')->findOrFail($id);
        $user = $client->user;
        $client->forceDelete();
        $user->forceDelete();
        
        return response()->json([
            'message' => 'Client deleted',
            'client'  => $client->id,
            'user'    => $user->id,
        ]);
    });

    Route::apiResource('rooms', RoomsController::class);
});
