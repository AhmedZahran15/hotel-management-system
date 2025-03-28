<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Pest\Plugins\Profile;

class ReceptionistManagerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'creator_user_id' => $this->creator_user_id,
            'banned_at' => $this->banned_at,
            'profile' => new ProfileResource($this->whenLoaded('profile')),
            // 'creator' is not included for managers
        ];    }
}
