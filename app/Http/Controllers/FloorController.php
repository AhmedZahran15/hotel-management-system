<?php

namespace App\Http\Controllers;

use App\Http\Resources\FloorManagerResource;
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
            return Inertia::render("",["users"=> FloorManagerResource::collection(User::paginate(10))]);
        else if(Auth::user()->hasRole("admin"))
            return Inertia::render("",["users"=> User::paginate(10)]);
        else
            return response()->json(['message' => 'Unauthorized'], 403);
    }

    public function create(){
        return Inertia::render("");
    }

    public function store(Request $request){
        $request -> validate([
            "name"=> ["required","string","min:3"],
            "creator_user_id"=>["required","int", new UserHasRoleOrPermission("role",["create floors"])],
        ]);
        Floor::create([
            "name"=> $request->name,
            "creator_user_id"=> $request->creator_user_id,
        ]);
        return back()->with("success","floor created");
    }

    public function show($id){
        $floor = Floor::findOrFail($id);
        return Inertia::render("",["floor"=> $floor]);
    }

    public function edit($id){
        $floor = Floor::findOrFail($id);
        if (!Auth::check() || Auth::id() !== $floor->creator_user_id)
            return response()->json(['message' => 'Unauthorized'], 403);

        return Inertia::render("",["floor"=> $floor]);


    }

    public function update(Request $request, $id){
        $floor = Floor::findOrFail($id);
        if (!Auth::check() || Auth::id() !== $floor->creator_user_id)
            return response()->json(['message' => 'Unauthorized'], 403);

        $request -> validate([
            "name"=> ["required","string","min:3"],
        ]);

        $floor->update(["name"=>$request->name]);

        return back()->with("success","floor updated");
    }

    public function destroy($id){
        $floor = Floor::findOrFail($id);
        if (!Auth::check() || Auth::id() !== $floor->creator_user_id)
            return response()->json(['message' => 'Unauthorized'], 403);

        $floor->delete();

        return back()->with("success","floor deleted");
    }

}
