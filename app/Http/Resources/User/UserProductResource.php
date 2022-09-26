<?php

namespace App\Http\Resources\User;

use App\Http\Resources\UserResource;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProductResource extends JsonResource
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
             'user' => new UserResource($this->user),
             'product' => new ProductResource($this->product),
             'created_at' => $this->created_at,
             'updated_at' => $this->updated_at
           ];
        }
    }
}
