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
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\Filters\Filter;

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
        $query = QueryBuilder::for(Client::class)
        ->allowedFilters([
            AllowedFilter::partial('name'),
            AllowedFilter::custom('country', new class implements Filter {
                public function __invoke(Builder $query, $value, string $property) {
                    $query->whereHas('countryInfo', function ($q) use ($value) {
                        $q->where('name', 'like', "%{$value}%"); // Assuming country name is stored in `name` column
                    });
                }
            }),
            AllowedFilter::custom('email', new class implements Filter {
                public function __invoke(Builder $query, $value, string $property)
                {
                    $query->whereHas('user', function ($q) use ($value) {
                        $q->where('email', 'like', "%{$value}%");
                    });
                }
            }),
        ])
        ->allowedSorts([
        'name',
        "id",
        "gender",
        AllowedSort::custom('email', new class implements \Spatie\QueryBuilder\Sorts\Sort {
            public function __invoke(Builder $query, bool $descending, string $property) {
                $direction = $descending ? 'desc' : 'asc';
                $query->orderBy(
                    DB::raw('(SELECT email FROM users WHERE users.id = clients.user_id)'),
                    $direction
                );
            }
        }),
        AllowedSort::custom('country.name', new class implements \Spatie\QueryBuilder\Sorts\Sort {
            public function __invoke(Builder $query, bool $descending, string $property) {
                $direction = $descending ? 'desc' : 'asc';
                $query->orderBy(
                    DB::raw('(SELECT name FROM countries WHERE countries.id = clients.country)'),
                    $direction
                );
            }
        }),
    ])->with(['user', 'phones', 'countryInfo']);

        if (Auth::user() && Auth::user()->hasRole('receptionist')) {
            $query->whereNull('approved_by');
        }

        $clients = ClientResource::collection($query->paginate(10));

        return Inertia::render('Admin/ManageClients', [
            'clients' => $clients,
            'countries' => $countries,
        ]);

    }

    // return  clietns that are approved by the logged in user
    public function approved()
    {

        $query = QueryBuilder::for(Client::class)
        ->allowedFilters([
            AllowedFilter::partial('name'),
            AllowedFilter::custom('country', new class implements Filter {
                public function __invoke(Builder $query, $value, string $property) {
                    $query->whereHas('countryInfo', function ($q) use ($value) {
                        $q->where('name', 'like', "%{$value}%"); // Assuming country name is stored in `name` column
                    });
                }
            }),
            AllowedFilter::custom('email', new class implements Filter {
                public function __invoke(Builder $query, $value, string $property)
                {
                    $query->whereHas('user', function ($q) use ($value) {
                        $q->where('email', 'like', "%{$value}%");
                    });
                }
            }),
        ])
        ->allowedSorts([
        'name',
        "id",
        "gender",
        AllowedSort::custom('email', new class implements \Spatie\QueryBuilder\Sorts\Sort {
            public function __invoke(Builder $query, bool $descending, string $property) {
                $direction = $descending ? 'desc' : 'asc';
                $query->orderBy(
                    DB::raw('(SELECT email FROM users WHERE users.id = clients.user_id)'),
                    $direction
                );
            }
        }),
        AllowedSort::custom('country.name', new class implements \Spatie\QueryBuilder\Sorts\Sort {
            public function __invoke(Builder $query, bool $descending, string $property) {
                $direction = $descending ? 'desc' : 'asc';
                $query->orderBy(
                    DB::raw('(SELECT name FROM countries WHERE countries.id = clients.country)'),
                    $direction
                );
            }
        }),
    ])->with(['user', 'phones', 'countryInfo']);
       $query->where("approved_by", Auth::id())->paginate(10);

        $clients = ClientResource::collection($query->paginate(10));


        return Inertia::render("HotelManagement/ManageApprovedClients", ["approved_clients" => $clients]);
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
                "phone_number" => $request->phone,
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
        $client = Client::with(['user', 'phones'])->findOrFail($id);
        $client->phones()->delete();
        $client->delete();
        $client->user->delete();
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
        if (!Auth::check() || !Auth::user()->hasAnyPermission(['approve clients','view clients' ,'manage clients'])) {
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
