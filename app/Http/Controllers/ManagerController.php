<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreManagerRequest;
use App\Http\Requests\UpdateManagerRequest;
use App\Http\Resources\UserResource;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ManagerController extends Controller
{

    public function index(): Response
    {
        $query = QueryBuilder::for(User::class)->role('manager')
            ->allowedFilters([
            AllowedFilter::partial('name'),
            AllowedFilter::partial('email'),
            ])
        ->allowedSorts(['id','name', 'email',])
        ->with(['profile']);

        return Inertia::render('Admin/ManageManagers', ['managers' => UserResource::collection( $query->paginate(10))]);
        }

    //will be tested later
    public function create()
    {
        // return Inertia::render('Managers/Create');
    }

    public function store(StoreManagerRequest $request): RedirectResponse
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
        //assign role
        $user->assignRole('manager');

        //handle avatar image upload
        if ($request->file("avatar_image")) {
            $user->updateAvatar($request->file("avatar_image"));
        }

        //create the associated profile with the user
        $user->profile()->create([
            'name' => $data['name'],
            'national_id' => $data['national_id'],
            'user_id' => $user->id,
            'creator_user_id' => Auth::id(),
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

    public function update(UpdateManagerRequest $request, User $manager): RedirectResponse
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
        if ($request->hasFile("avatar_image")) {
            $manager->updateAvatar($request->file("avatar_image"));
        }
        //update the associated profile with the user
        $manager->profile()->update([
            'name' => $data['name'],
            'national_id' => $data['national_id'],
        ]);
        return redirect()->back()->with('success', 'Manager updated successfully.');
    }

    public function destroy(User $manager): RedirectResponse
    {
        $manager->delete();
        return redirect()->back()->with('success', 'Manager deleted successfully.');
    }
}
