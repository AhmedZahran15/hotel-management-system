<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\Phone;
use App\Models\User;
use App\Notifications\ClientApprovedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Nnjeim\World\World;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //send countries to the front end to be used in the select input
        $countries = Cache::remember('countries', now()->addMonth(), function () {
            $response = World::countries();
            return $response->success ? $response->data : [];
        });

        //return clients based on the user role
        if (Auth::user() && Auth::user()->hasAnyRole(["admin", "manager",'receptionist'])) {
            $clients = ClientResource::collection(Client::with("user", 'phones',"approvedBy")->paginate(10));
        } 
        return Inertia::render("Admin/ManageClients",
        ["clients" => $clients,
        'countries' => $countries,
        'type' => 'unapproved',
    ]);

    }

    // return  clietns that are approved by the logged in user
    public function approved()
    {
        return Inertia::render("Admin/ManageClients", [
            "approved_clients" => ClientResource::collection(Client::with('user', 'phones')->where("approved_by", Auth::id())->get())
            ,'type' => 'approved'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //return Inertia::render("");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => ["required", "string", "min:3"],
            "avatar_image" => ["image", "mimes:jpg,jpeg", "max:2048"],
            "country" => ["required", "string"],
            "gender" => ["required", "string", "in:male,female"],
            "email" => ["required", "string", "email", "unique:users",],
            "password" => ["required", "string", "min:8", "confirmed"],
            'phone' => ['sometimes', 'string', 'regex:/^\+?[0-9]{7,}$/'],
        ]);
        //handling adding user:
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "user_type" => "client",
        ]);

        $user->assignRole("client");

        //handle profile picture
        if ($request->file("avatar_image")) {
            $user->updateAvatar($request->file("avatar_image"));
        }

        //handle client creation
        $client = Client::create([
            "name" => $request->name,
            "country" => $request->country,
            "gender" => $request->gender,
            "user_id" => $user->id,
        ]);
        //handle phone;
        if ($request->phone) {
            $phone = Phone::create([
                "phone" => $request->phone,
                "client_id" => $client->id
            ]);
        }
        return back()->with("success", "Client created successfully");
    }

    /**
     * Display the specified resource.
     */
    public function show($client)
    {
        $client = new ClientResource(Client::findOrFail($client));
        return Inertia::render("", ["client" => $client]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $client = new ClientResource(Client::with("user")->findOrFail($id));
        return Inertia::render("", ["employee" => $client]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $client = Client::with("user")->findOrFail($id);
        $request->validate([
            "name" => ["required", "string", "min:3"],
            "country" => ["required", "string"],
            "gender" => ["required", "string", "in:male,female"],
            "email" => ["required", "string", Rule::unique("users")->ignore($client->user->id)],
            "password" => ["sometimes", "string", "min:8", "confirmed"],
            "avatar_image" => ["sometimes", "image", "mimes:jpg,jpeg", "max:2048"],
        ]);

        $client->update(["name" => $request->name, "country" => $request->country, "gender" => $request->gender]);
        $client->user->update(["email" => $request->email]);

        if ($request->hasFile("avatar_image")) {
            $client->user->updateAvatar($request->file("avatar_image"));
        }

        if ($request->filled("password")) {
            $client->user->update(["password" => Hash::make($request->password)]);
        }

        return back()->with("success", "Client updated successfully");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {

        $selfdelete = false;
        if (Auth::user()->id == $id) {
            $selfdelete = true;
        }
        $client = Client::with("user")->findOrFail($id);
        $user = $client->user;
        $client->forceDelete();
        $user->forceDelete();
        //case the user deleted his own account
        if ($selfdelete) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/');
        }
        //case an addmin or manager delted it
        return back()->with("success", "Client deleted successfully");
    }

    /**
     * Approve a client account
     */
    public function approve(Client $client)
    {
        // Ensure only authorized users can approve clients
        if (!Auth::check() || !Auth::user()->hasAnyPermission(['approve clients', 'manage clients'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only update if not already approved
        if ($client->approved_by === null) {
            $client->update([
                'approved_by' => Auth::id(),
            ]);
            $client->user->notify(new ClientApprovedNotification($client));
            return back()->with('success', 'Client has been approved successfully and an email has been sent to the client.');
        }

        return back()->with('info', 'Client was already approved');
    }


}
