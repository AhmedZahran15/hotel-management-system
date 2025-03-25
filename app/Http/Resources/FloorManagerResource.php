<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FloorManagerResource extends JsonResource
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
            "name"=> $this->name,
            "roomsCount" => $this->whenLoaded('rooms', fn() => $this->rooms->count()),
            "reservedRoomsCount" => $this->whenLoaded('rooms', fn() => $this->rooms->where('state', "occupied")->count()),
            "availabledRoomsCount" => $this->whenLoaded('rooms', fn() => $this->rooms->where('state', "available")->count()),
            "manager_id" => $this->creator_user_id,
        ];
    }
}
