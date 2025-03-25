<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoomAdminResource;
use App\Http\Resources\RoomManagerResource;
use App\Models\Room;
use App\Rules\UserHasRoleOrPermission;
use Illuminate\Broadcasting\Broadcasters\AblyBroadcaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = QueryBuilder::for(Room::class)
        ->allowedFilters([
            AllowedFilter::partial('number'),
            AllowedFilter::exact('capacity'),
            AllowedFilter::exact('room_price'),
            AllowedFilter::exact('state'),
            AllowedFilter::exact('floor_number'),
            AllowedFilter::exact('Managere'),
        ])
        ->allowedSorts(['number', 'capacity','state','room_price','floor_number','manager_name'])
        ->join('users', 'rooms.creator_user_id', '=', 'users.id') // ✅ Join users table
        ->select('rooms.*', 'users.name as manager_name') // ✅ Select derived columns
        ->with(['floor','creatorUser']);
        if(Auth::user()->hasRole("manager"))
            $rooms=RoomManagerResource::collection($query->paginate(10));
        else if(Auth::user()->hasRole("admin"))
            $rooms = RoomAdminResource::collection($query->paginate(10));
        return Inertia::render("HotelManagement/ManageRooms",["rooms"=> $rooms]);

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
        $request -> validate([
            "floor_number"=>["required","int",Rule::exists("floors","number")],
            "number"=>["required","integer","min_digits:4", Rule::unique('rooms','number')],
            "capacity"=>["required","integer","max:5"],
            "room_price"=>["required","integer",],
            "state"=>["required","in:available,occupied,being_reserved,maintenance",],
        ]);
        $request["creator_user_id"]= Auth::user()->id;
        $room = Room::create($request->all());

        return back()->with("success","floor created");

    }

    /**
     * Display the specified resource.
     */
    public function show($room)
    {
        // $room = Room::with(["creatorUser","floor"])->where("number",$room)->firstOrFail();
        // if(Auth::user()->hasRole("manager")){
        //     return Inertia::render("",["rooms"=> new RoomManagerResource($room)]);
        // }
        // else if(Auth::user()->hasRole("admin")){
        //     return Inertia::render("",["rooms"=> new RoomAdminResource($room)]);
        // }
        // else
        //     return response()->json(['message' => 'Unauthorized'], 403);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($room)
    {
        $room = Room::with(["creatorUser","floor"])->where("number",$room)->firstOrFail();
        if (!Auth::check() || (!Auth::user()->hasRole("admin") && Auth::id() !== $$room->creator_user_id))
            return response()->json(['message' => 'Unauthorized'], 403);

        if(Auth::user()->hasRole('admin'))
            return Inertia::render("",["room"=> new RoomAdminResource($room)]);
        else
            return Inertia::render("",["room"=> new RoomManagerResource($room)]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        dd($request);
        // $room = Room::with(["creatorUser","floor"])->where("number",$room)->firstOrFail();
        $request -> validate([
            "floor_number"=>["required","int",Rule::exists("floors")],
            "number"=>["required","integer","min_digits:4", Rule::unique('rooms')->ignore($room->number)],
            "capacity"=>["required","integer","max:5"],
            "room_price"=>["required","integer",],
            "state"=>["required","in:available,occupied,being_reserved,maintenance",],
        ]);
        $room->update($request->all());

        return back()->with("success","floor updated");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {

        if (!Auth::check() || (!Auth::user()->hasRole("admin") && Auth::id() !== $room->creator_user_id))
            abort(403);
        if($room->reservations()?->count() ?? 1 > 0)
            return back()->withErrors(["Can't delete"=>"room can't be deleted because it's currently reserved"]);

        $room->delete();

        return back()->with("success","room deleted successfuly");
    }
}
