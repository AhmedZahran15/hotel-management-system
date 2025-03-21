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
            "profile_picture_url"=> "/clients/image/".$this->id,
            "country"=> $this->country,
            "gender"=> $this->gender,
            "user" => new UserResource($this->whenLoaded('user')),
            "email"=>$this->user->email,
            "approved_by"=>$this->approved_by,
        ];
    }
}
