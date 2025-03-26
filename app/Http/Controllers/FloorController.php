<?php

namespace App\Http\Controllers;

use App\Http\Resources\FloorAdminResource;
use App\Http\Resources\FloorManagerResource;
use App\Http\Resources\RoomAdminResource;
use App\Http\Resources\RoomManagerResource;
use App\Models\Floor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Rules\UserHasRoleOrPermission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;


class FloorController extends Controller
{
    public function index(){
        $query = QueryBuilder::for(Floor::class)
        ->allowedFilters([
            AllowedFilter::partial('number'),
            AllowedFilter::partial('name'),
        ])
        ->allowedSorts(['number', 'name'])
        ->with('rooms');

    if (Auth::user()->hasRole("manager")) {
        $floors = FloorManagerResource::collection($query->paginate(10));
    } elseif (Auth::user()->hasRole("admin")) {
        $floors = FloorAdminResource::collection(
            $query->with('creatorUser')->paginate(10)
        );
    }

    return Inertia::render("HotelManagement/ManageFloors", ["floors" => $floors]);
}

    public function create(){
        // return Inertia::render("");
    }

    public function store(Request $request){
        $request -> validate([
            "name"=> ["required","string","min:3"],
        ]);
        Floor::create([
            "name"=> $request->name,
            "creator_user_id"=> Auth::user()->id,
        ]);
        return back()->with("success","floor created");
    }

    public function show($floor){
        $floor = Floor::with(["creatorUser"])->where('number', $floor)->firstOrFail();
        if(Auth::user()->hasRole("manager")){
            // return Inertia::render("",["floor"=> new FloorManagerResource($floor)]);
        }
        else if(Auth::user()->hasRole("admin")){
            // return Inertia::render("",["floor"=> new FloorAdminResource($floor)]);
        }
    }

    public function edit($id){
        $floor = Floor::findOrFail($id);
        if (!Auth::check() || (!Auth::user()->hasRole("admin") && Auth::id() !== $floor->creator_user_id))
            return response()->json(['message' => 'Unauthorized'], 403);

        // return Inertia::render("",["floor"=> $floor]);


    }

    public function update(Request $request, $floorNum){
        $floor = new FloorManagerResource(Floor::where('number', $floorNum)->firstOrFail());
        // if (!Auth::check() || (!Auth::user()->hasRole("admin") && Auth::id() !== $floor->creator_user_id))
        //     abort(304);

        $request -> validate([
            "name"=> ["required","string","min:3"],
        ]);

        $floor->update(["name"=>$request->name]);

        return back()->with("success","floor updated");
    }

    public function destroy(Floor $floor){

        if (!Auth::check() || (!Auth::user()->hasRole("admin") && Auth::id() !== $floor->creator_user_id))
            abort(403);
        if($floor->rooms()->count() > 0){
            return to_route("floors.index")->withErrors(["error"=> "Can't delete floor because it has rooms attached to it."]);

        }
        $floor->delete();
        return back()->with("success","floor deleted");
    }

}
