<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SchoolResource extends JsonResource
{
    public function toArray($request)
    {
        if ($this->resource == null) {
            return null;
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'city' => new CityResource($this->city),
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'logo' => $this->logo,
            'description' => $this->description,
        ];
    }
}
