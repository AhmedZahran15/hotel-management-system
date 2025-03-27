<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof Client) {
            return new  ClientResource( $this->resource)->toArray($request);
        } elseif ($this->resource instanceof Employee) {
            return new EmployeeResource($this->resource)->toArray($request);
        }

        return [];
    }
}
