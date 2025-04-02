<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReceptionistRequest;
use App\Http\Requests\UpdateReceptionistRequest;
use App\Http\Resources\ReceptionistAdminResource;
use App\Http\Resources\ReceptionistManagerResource;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ReceptionistController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = QueryBuilder::for(User::class)->role('receptionist')
            ->allowedFilters([
            AllowedFilter::partial('name'),
            AllowedFilter::partial('email'),
            ])
        ->allowedSorts(['name', 'email','id', 'created_at'])
        ->with(['profile', 'creator']);

        $resource = $user->hasRole('admin') ? ReceptionistAdminResource::class : ReceptionistManagerResource::class;

        $resiptionists = $resource::collection($query->paginate(10));

        return Inertia::render('Admin/ManageReceptionists', ['receptionists' => $resiptionists]);
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
        if ($request->file("avatar_image")) {
            $user->updateAvatar($request->file("avatar_image"));
        }

        //create the associated profile with the user
        $user->profile()->create([
            'name' => $data['name'],
            'national_id' => $data['national_id'],
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
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        //update password if it is not empty
        if (!empty($data['password'])) {
            $receptionist->update(['password' => Hash::make($data['password'])]);
        }

        //handled avatar image update
        if ($request->hasFile("avatar_image")) {
            $receptionist->updateAvatar($request->file("avatar_image"));
        }
        //update the associated profile with the user
        $receptionist->profile()->update([
            'name' => $data['name'],
            'national_id' => $data['national_id'],
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
