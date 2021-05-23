<?php

namespace App\Http\Resources\User;

use App\Http\Resources\ProductDetailVideosResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderVideoDetailResource extends JsonResource
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
                'id' => $this->id,
                'orderDetail' => new OrderDetailResource($this->orderDetail),
                //'productDetailVideo' => new ProductDetailVideosResource($this->productDetailVideo),
                'productDetailVideo' => $this->productDetailVideo,
                'price' => $this->price,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}
