<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'number' => $this->number,
            'name' => $this->title,
            'description' => $this->description,
            'capacity' => $this->capacity,
            'price_formatted' => '$' . number_format($this->room_price / 100, 2),
            'state' => $this->state,
            'floor_number' => $this->floor_number,
            'image_url' => $this->when($this->getFirstMedia('room_images'), function() {
                return $this->getFirstMedia('room_images')->getUrl();
            }),
        ];
    }
}
