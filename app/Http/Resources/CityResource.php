<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if($this->resource != null){
            return [
                'id' => $this->id,
                'name' => $this->name,
                'provinces_id' => ($this->province) ? $this->province->name : '-',
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}
