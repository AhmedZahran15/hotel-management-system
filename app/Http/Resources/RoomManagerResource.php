<?php

namespace App\Http\Resources;

use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomManagerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
           "number"=> $this->number,
            "capacity"=> $this->capacity,
            "room_price"=> $this->room_price,
            "state" => $this->state,
            "floor"=> new FloorManagerResource(Floor::find($this->floor_number)),
        ];
    }
}
