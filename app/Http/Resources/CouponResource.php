<?php

namespace App\Http\Resources;

use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
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
                'description' => $this->description,
                'amount' => $this->amount,
                'type' => $this->type,
                'expired_at' => $this->expired_at,
                'product' => new ProductResource($this->product), 
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}
