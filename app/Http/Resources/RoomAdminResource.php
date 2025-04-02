<?php

namespace App\Http\Resources;

use App\Models\Floor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomAdminResource extends JsonResource
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
            "room_price"=> $this->room_price/100,
            "state" => $this->state,
            "floor_number"=>$this->floor_number,
            "floor"=> new FloorAdminResource($this->whenLoaded("floor")),
            "manager"=>new UserResource($this->whenLoaded("creatorUser")),
            "manager_id"=>$this->creator_user_id,
            "title"=>$this->title,
            "description"=>$this->description,
            "image_url"=>$this->getImageUrl(),

            ];
    }
}
