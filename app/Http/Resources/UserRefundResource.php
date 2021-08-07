<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserRefundResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if($this->resource != null) {
            return [
              'id' => $this->id,
              'first_name' => $this->first_name != null ? $this->first_name : '',
              'lastname' => $this->last_name != null ? $this->last_name : ''
            ];
        }
    }
}
