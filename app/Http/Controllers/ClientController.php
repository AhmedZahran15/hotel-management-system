<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\Phone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::user() && Auth::user()->hasAnyRole(["admin","manager"])) {
            return Inertia::render("Admin/ManageClients",["clients"=> ClientResource::collection(Client::with("user",'phones')->paginate(10))]);
        }
        else if(Auth::user()->hasRole("receptionist")) {
            return Inertia::render("Admin/ManageClients",
            ["clients"=> ClientResource::collection(Client::with("user")->whereNull("approved_by")->paginate(10)),
                    "approved_clients"=> ClientResource::collection(Client::with('user','phones')->where("approved_by",Auth::user()->id)->get()) ]);
        }
        else
            return response()->json(['message' => 'Unauthorized'], 403);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render("");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name"=>["required","string","min:3"],
            "avatar_image"=>["image","mimes:jpg,jpeg","max:2048"],
            "country"=>["required","string"],
            "gender"=>["required","string","in:male,female"],
            "email"=>["required","string","email","unique:users",],
            "password"=>["required","string","min:8","confirmed"],
            'phone' => ['sometimes', 'string', 'regex:/^\+?[0-9]{7,}$/'],
        ]);
        //handling adding user:
        $user = User::create([
            "name"=> $request->name,
            "email"=> $request->email,
            "password"=> Hash::make($request->password),
            "user_type"=>"client",
        ]);

        $user->assignRole("client");

        //handle profile picture
        $filename = null;
        if($request->file("avatar_image")){
            $storagePath = config('app.client_avatar_storage_path');
            $extension = $request->file('avatar_image')->getClientOriginalExtension();
            $filename = "client-{$user->id}.{$extension}";
            $request->file("avatar_image")->storeAs($storagePath,$filename,"local"); // NeedEdit: add env var
        }

        //handle client creation
        $client = Client::create([
            "name"=> $request->name,
            "country"=> $request->country,
            "gender"=> $request->gender,
            "user_id"=> $user->id,
            "img_name" => $filename ?? "default.jpg"
        ]);
        //handle phone;
        if($request->phone){
            $hpone = Phone::create([
                "phone"=> $request->phone,
                "client_id"=>$client->id
            ]);
        }
        return back()->with("success","Client created successfully");
    }

    /**
     * Display the specified resource.
     */
    public function show($client)
    {
        $client = new ClientResource(Client::findOrFail( $client ));
        return Inertia::render("", ["client"=> $client]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $client = new ClientResource(Client::with("user")->findOrFail( $id ));
        return Inertia::render("",["employee"=> $client]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $client = Client::with("user")->findOrFail( $id );
        $request->validate([
            "name"=>["required","string","min:3"],
            "country"=>["required","string"],
            "gender"=>["required","string","in:male,female"],
            "email"=>["required","string",Rule::unique("users")->ignore($client->user->id)],
            "password"=>["sometimes","string","min:8","confirmed"],
            "avatar_image"=>["sometimes","image","mimes:jpg,jpeg","max:2048"],
        ]);

        $client->update(["name"=>$request->name,"country"=>$request->country, "gender"=>$request->gender]);
        $client->user->update(["email"=>$request->email]);

        if($request->hasFile("avatar_image")){
            $storagePath = config('app.client_avatar_storage_path');
            Storage::disk("local")->delete("$storagePath"."/".$client->img_name);
            $request->file("avatar_image")->storeAs($storagePath,$client->img_name,"local"); // NeedEdit: add env var
        }

        if($request->filled("password")){
            $client->user->update(["password"=> Hash::make($request->password)]);
        }

        return back()->with("success","Client updated successfully");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id,Request $request)
    {

            $client = Client::with("user")->findOrFail( $id );
            $user = $client->user;
            $client->delete();
            $user->delete();
            if ($client->img_name !== "default.jpg") {
                $storagePath = config('app.client_avatar_storage_path');
                Storage::disk("local")->delete($storagePath."/".$client->img_name);
            }

            //case the user deleted his own account
            if(Auth::user()->id == $id)
            {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect('/');
            }

            //case an addmin or manager delted it
            return back()->with("success","Client deleted successfully");
        }

    }
