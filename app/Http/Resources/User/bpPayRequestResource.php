<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class bpPayRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->resource != null) {
            return [
                
            ];
        }
    }
}
