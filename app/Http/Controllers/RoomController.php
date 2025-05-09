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
use Spatie\QueryBuilder\AllowedSort;
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
            ])
            ->allowedSorts(['number', 'capacity','state','room_price','floor_number',
                AllowedSort::callback('manager.name', function ($query, string $direction) {
                $direction = strtolower($direction) === '1' ? 'desc' : 'asc';
                $query->join('users', 'rooms.creator_user_id', '=', 'users.id')
                ->select('rooms.*', 'users.name as manager_name')
                ->orderBy('users.name', $direction);
                }),
        ])
        ->with(['floor','creatorUser']);

        // Apply different resource collections dynamically
        $resource = Auth::user()->hasRole('manager') ? RoomManagerResource::class : RoomAdminResource::class;

        $rooms = $resource::collection($query->paginate(10));

        return Inertia::render("HotelManagement/ManageRooms", ["rooms" => $rooms]);

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
            "room_price"=>["required","integer","min:10"],
            "state"=>["required","in:available,occupied,being_reserved,maintenance",],
            "title"=>["required","string","max:255",'min:5'],
            "description"=>["required","string",'min:5'],
            'image' => ['required', 'image', 'mimes:jpeg,jpg', 'max:2048'],
        ]);
        $request["creator_user_id"]= Auth::user()->id;
        $room = Room::create(
            $request->only('floor_number','number','capacity','room_price','state','title','description','creator_user_id')
        );
        $room->updateImage($request->file('image'));
        return back()->with("success","Reoom created successfuly");

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

        // $room = Room::with(["creatorUser","floor"])->where("number",$room)->firstOrFail();
        $request -> validate([
            "floor_number"=>["required","int",Rule::exists("floors","number")],
            "number"=>["required","integer","min_digits:4", Rule::unique('rooms', 'number')->ignore($room->number, 'number')],
            "capacity"=>["required","integer","max:5"],
            "room_price"=>["required","integer",],
            "state"=>["required","in:available,occupied,being_reserved,maintenance"],
            "title"=>["required","string","max:255",'min:5'],
            "description"=>["required","string",'min:5'],
            'image' => ['sometimes', 'image', 'mimes:jpeg,jpg', 'max:2048'],
        ]);
        $room->update($request->only('floor_number','number','capacity','room_price','state','title','description'));
        if($request->file('image'))
            $room->updateImage($request->file('image'));

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
