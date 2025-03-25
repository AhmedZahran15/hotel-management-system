<?php

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    $user = User::where('email', $request->email)->first();
    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }
    return $user->createToken($request->device_name)->plainTextToken;
});

//delete user using api call
Route::delete('/clietns/{id}', function ($id) {
    $client = Client::with("user")->findOrFail($id);
    $user = $client->user;
    $client->forceDelete();
    $user->forceDelete();
    return response()->json([
        "message" => "Client deleted",
        "client" => $client->id,
        "user" => $user->id,
    ]);
})->middleware("auth:sanctum");
