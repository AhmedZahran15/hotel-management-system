<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReceptionistRequest;
use App\Http\Requests\UpdateReceptionistRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
            'creator_user_id' => Auth::id(),
        ]);

        //assgin role to user
        $user->assignRole('receptionist');

        //handle avatar image upload
        $filename = null;
        if($request->file("avatar_image")){
            $storagePath = config('app.emp_avatar_storage_path');
            $extension = $request->file('avatar_image')->getClientOriginalExtension();
            $filename = "user-{$user->id}.{$extension}";
            $request->file("avatar_image")->storeAs($storagePath,$filename,"local"); // NeedEdit: add env var
        }

        //create the associated profile with the user
        $user->profile()->create([
            'name' => $data['name'],
            'national_id' => $data['national_id'],
            'img_name' => $data['avatar_image'] ?? 'default.jpg',
            'creator_user_id' => Auth::id(),
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
        //ensure that the manger only can update his receptionists
        if (!auth()->user()->hasRole('admin') && $receptionist->creator_user_id !== auth()->id()) {
            abort(403, 'You do not have permission to update this receptionist.');
        }

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

         //handled avatar image update
         if($request->hasFile("avatar_image")){
            $storagePath = config('app.emp_avatar_storage_path');
            Storage::disk("local")->delete("$storagePath"."/".$receptionist->profile->img_name);
            $request->file("avatar_image")->storeAs($storagePath,$receptionist->profile->img_name,"local"); // NeedEdit: add env var
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
        if ($receptionist->profile->img_name !== "default.jpg") {
            $storagePath = config('app.emp_avatar_storage_path');
            Storage::disk("local")->delete($storagePath."/".$receptionist->profile->img_name);
        }
        $receptionist->delete();
        return redirect()->route('receptionists.index')
        ->with('success', 'Receptionist deleted successfully.');
    }
}
