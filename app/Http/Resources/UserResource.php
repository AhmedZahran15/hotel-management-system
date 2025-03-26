<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            "name"=>$this->name,
            "email"=>$this->email,
            'user_type'=>$this->user_type,
            'banned'=>$this->banned_at? true : false,
            'avatar_image'=>$this->getAvatarUrl()
        ];
    }
}
