<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductForOrderDetailResource extends JsonResource
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
                'name' => $this->name,
                'type' => $this->type,
                'main_image_thumb_path' => $this->main_image_thumb_path
            ];
        }
       
       
    }
}
