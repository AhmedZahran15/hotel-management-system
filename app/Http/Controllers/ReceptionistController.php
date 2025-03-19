<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReceptionistRequest;
use App\Http\Requests\UpdateReceptionistRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ReceptionistController extends Controller
{
    public function index()
    {
        $receptionists = User::role('receptionist')->with('profile')->paginate(10);
        // return Inertia::render('Receptionists/Index', ['receptionists' => $receptionists]);
        return $receptionists;
    }

    public function create()
    {
        // return Inertia::render('Receptionists/Create');
    }

    public function store(StoreReceptionistRequest $request): RedirectResponse
    {
        $data = $request->validated();

        //create user first
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'user_type' => 'employee',
            'creator_user_id' => auth()->id(),
        ]);

        //assgin role to user
        $user->assignRole('receptionist');

        //create the associated profile with the user
        $user->profile()->create([
            'name' => $data['name'],
            'national_id' => $data['national_id'],
            'img_name' => $data['avatar_image'] ?? 'default.jpg',
            'creator_user_id' => auth()->id(),
        ]);

        return redirect()->route('receptionists.index')
        ->with('success', 'Receptionist created successfully.');
    }

    public function show(User $receptionist)
    {
        // return Inertia::render('Receptionists/Show', ['receptionist' => $receptionist->load('profile')]);
        return $receptionist->load('profile');
    }

    public function edit(User $receptionist)
    {
        // return Inertia::render('Receptionists/Edit', ['receptionist' => $receptionist->load('profile')]);
    }

    public function update(UpdateReceptionistRequest $request, User $receptionist): RedirectResponse
    {
        $data = $request->validated();

        //update user
        $receptionist->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        //update password if it is not empty
        if (!empty($data['password'])) {
            $receptionist->update(['password' => Hash::make($data['password'])]);
        }

        //update the associated profile with the user
        $receptionist->profile()->update([
            'name'        => $data['name'],
            'national_id' => $data['national_id'],
            'img_name'    => $data['avatar_image'] ?? $receptionist->profile->img_name,
        ]);

        return redirect()->route('receptionists.index')
        ->with('success', 'Receptionist updated successfully.');
    }

    public function destroy(User $receptionist): RedirectResponse
    {
        $receptionist->delete();
        return redirect()->route('receptionists.index')
        ->with('success', 'Receptionist deleted successfully.');
    }
}
