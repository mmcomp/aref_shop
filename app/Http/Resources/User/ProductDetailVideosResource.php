<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\VideoSessionsResource;

class ProductDetailVideosResource extends JsonResource
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
                'id' => $this->id
            ];
        }
    }
}
