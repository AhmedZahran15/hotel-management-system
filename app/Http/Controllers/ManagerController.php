<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreManagerRequest;
use App\Http\Requests\UpdateManagerRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;


class ManagerController extends Controller
{
    //✅
    public function index() :Response
    {
        $managers=User::role('manager')->with('profile')->paginate(10);
        // return Inertia::render('Managers/Index', ['managers' => $managers]);
        return $managers;
    }

    //will be tested later
    public function create()
    {
        // return Inertia::render('Managers/Create');
    }

    public function store(StoreManagerRequest $request) : RedirectResponse
    {
        $data = $request->validated();
        //create user first
        $user=User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'user_type' => 'employee',
            'creator_user_id' => Auth::id(),
        ]);
        //assign role
        $user->assignRole('manager');

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
            'img_name' => $filename ?? 'default.jpg',
            'user_id' => $user->id,
            'creator_user_id' => auth()->id(),
        ]);

        return redirect()->route('managers.index')->with('success', 'Manager created successfully.');
    }

    //✅
    public function show(User $manager)
    {
        // return Inertia::render('Managers/Show', ['manager' => $manager ->load('profile')]);
        return $manager->load('profile');
    }

    //will be tested later
    public function edit(User $manager)
    {
        // return Inertia::render('Managers/Edit', ['manager' => $manager ->load('profile')]);
    }

    public function update(UpdateManagerRequest $request, User $manager) : RedirectResponse
    {
        $data = $request->validated();
        //update user
        $manager->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
        //optional password update
        if (!empty($data['password'])) {
            $manager->update(['password' => Hash::make($data['password'])]);
        }
        //handled avatar image update
        if($request->hasFile("avatar_image")){
            $storagePath = config('app.emp_avatar_storage_path');
            Storage::disk("local")->delete("$storagePath"."/".$manager->profile->img_name);
            $request->file("avatar_image")->storeAs($storagePath,$manager->profile->img_name,"local"); // NeedEdit: add env var
        }
        //update the associated profile with the user
        $manager->profile()->update([
            'name' => $data['name'],
            'national_id' => $data['national_id'],
            'img_name' => $data['avatar_image'] ?? $manager->profile->img_name,
        ]);
        return redirect()->route('managers.index')->with('success', 'Manager updated successfully.');
    }

    public function destroy(User $manager) : RedirectResponse
    {
        if ($manager->profile->img_name !== "default.jpg") {
            $storagePath = config('app.emp_avatar_storage_path');
            Storage::disk("local")->delete($storagePath."/".$manager->profile->img_name);
        }
        $manager->delete();
        return redirect()->route('managers.index')->with('success', 'Manager deleted successfully.');
    }
}
