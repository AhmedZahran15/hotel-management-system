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

class FloorController extends Controller
{

    // we return back in update ,store and store  if we gonna call update from index page otherwise we will redirect to floor.index;
    public function index(){
        if(Auth::user()->hasRole("manager"))
            $floors = FloorManagerResource::collection(Floor::with("rooms")->paginate(10));
        else if(Auth::user()->hasRole("admin"))
            $floors = FloorAdminResource::collection(Floor::with('creatorUser','rooms')->paginate(10));

        return Inertia::render("HotelManagement/ManageFloors",["floors"=> $floors ]);
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
        else
            return response()->json(['message' => 'Unauthorized'], 403);
    }

    public function edit($id){
        $floor = Floor::findOrFail($id);
        if (!Auth::check() || (!Auth::user()->hasRole("admin") && Auth::id() !== $floor->creator_user_id))
            return response()->json(['message' => 'Unauthorized'], 403);

        // return Inertia::render("",["floor"=> $floor]);


    }

    public function update(Request $request, $floor){
        $floor = new FloorManagerResource(Floor::where('number', $floor)->firstOrFail());
        if (!Auth::check() || (!Auth::user()->hasRole("admin") && Auth::id() !== $floor->creator_user_id))
            abort(304);

        $request -> validate([
            "name"=> ["required","string","min:3"],
        ]);

        $floor->update(["name"=>$request->name]);

        return back()->with("success","floor updated");
    }

    public function destroy(Floor $floor){
        //$floor = new FloorManagerResource(Floor::where('number', $floor)->firstOrFail());
        if (!Auth::check() || (!Auth::user()->hasRole("admin") && Auth::id() !== $floor->creator_user_id))
            abort(403);
        if($floor->rooms()->count() > 0){
            //return back()->withErrors(["error" => "Can't delete floor because it has rooms attached to it."]);
            return to_route("floors.index")->withErrors(["error"=> "Can't delete floor because it has rooms attached to it."]);
            //return to_route("floors.index")->withErrors(['approval' => 'Your account is pending approval. You will be notified once your account is approved.']);

        }
        $floor->delete();
        return back()->with("success","floor deleted");
    }

}
