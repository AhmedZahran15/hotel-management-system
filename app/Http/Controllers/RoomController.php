<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoomAdminResource;
use App\Http\Resources\RoomManagerResource;
use App\Models\Room;
use App\Rules\UserHasRoleOrPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::user()->hasRole("manager"))
            return Inertia::render("",["rooms"=> RoomManagerResource::collection(Room::with("floor")->paginate(10))]);
        else if(Auth::user()->hasRole("admin"))
            return Inertia::render("",["rooms"=> RoomAdminResource::collection(Room::with(["floor","creatorUser"])->paginate(10))]);
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
        $request -> validate([
            "floor_number"=>["required","int",Rule::exists("floors")],
            "number"=>["required","integer","min_digits:4", Rule::unique('rooms')],
            "capacity"=>["required","integer","max:5"],
            "room_price"=>["required","integer",],
            "status"=>["required","in:available,occupied,being_reserved,maintenance",],
        ]);
        $request["creator_user_id"]= Auth::user()->id;
        $room = Room::create($request->all());

    }

    /**
     * Display the specified resource.
     */
    public function show($room)
    {
        $room = Room::with(["creatorUser","floor"])->where("number",$room)->firstOrFail();
        if(Auth::user()->hasRole("manager")){
            return Inertia::render("",["rooms"=> new RoomManagerResource($room)]);
        }
        else if(Auth::user()->hasRole("admin")){
            return Inertia::render("",["rooms"=> new RoomAdminResource($room)]);
        }
        else
            return response()->json(['message' => 'Unauthorized'], 403);
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
        // $room = Room::with(["creatorUser","floor"])->where("number",$room)->firstOrFail();
        $request -> validate([
            "floor_number"=>["required","int",Rule::exists("floors")],
            "number"=>["required","integer","min_digits:4", Rule::unique('rooms')->ignore($room->number)],
            "capacity"=>["required","integer","max:5"],
            "room_price"=>["required","integer",],
            "status"=>["required","in:available,occupied,being_reserved,maintenance",],
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
            return response()->json(['message' => 'Unauthorized'], 403);

        $room->delete();

        return back()->with("success","room deleted successfuly");
    }
}
