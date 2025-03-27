<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiRoomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'number' => $this->number,
            'capacity' => $this->capacity,
            'room_price' => $this->room_price,
            'state' => $this->state,
            'floor_number' => $this->floor_number,
            'creator_user_id' => $this->creator_user_id,
            'floor' => $this->whenLoaded('floor'),
        ];
    }
}
