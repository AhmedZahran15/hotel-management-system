<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            "id"=> $this->id,
            "name"=> $this->name,
            "country"=> $this->country,
            "gender"=> $this->gender,
            "user" => new UserResource($this->whenLoaded('user')),
            "email"=>$this->user->email,
            "approvedBy"=>new UserResource($this->whenLoaded("approved_by")),
            "approved_by"=>$this->approved_by,
            "phones" => $this->whenLoaded("phones", fn() => $this->phones->pluck('phone_number')->toArray()),

        ];
    }
}
