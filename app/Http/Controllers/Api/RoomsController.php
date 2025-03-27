<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreRoomRequest;
use App\Http\Requests\Api\UpdateRoomRequest;
use App\Http\Resources\ApiRoomResource;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RoomsController extends Controller
{
    public function index(): JsonResponse
    {
        $rooms = Room::with('floor')->get();

        return response()->json([
            'status' => 'success',
            'data'   => ApiRoomResource::collection($rooms),
        ]);
    }

    public function store(StoreRoomRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['creator_user_id'] = Auth::id();

        $room = Room::create($data);

        return response()->json([
            'status' => 'success',
            'data'   => new ApiRoomResource($room),
        ], 201);
    }


    public function show(Room $room): JsonResponse
    {
        //optionally if needed load the floor acossiated with the room
        // $room->load('floor');

        return response()->json([
            'status' => 'success',
            'data'   => new ApiRoomResource($room),
        ]);
    }

    public function update(UpdateRoomRequest $request, Room $room): JsonResponse
    {
        $room->update($request->validated());

        return response()->json([
            'status' => 'success',
            'data'   => new ApiRoomResource($room),
        ]);
    }

    public function destroy(Room $room): JsonResponse
    {
        if ($room->reservations()->count() > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot delete a room that is currently reserved.',
            ], 422);
        }

        $room->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Room deleted successfully.',
        ]);
    }
}
