<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CouponResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\User\ProductForOrderDetailResource;

class OrderDetailResource extends JsonResource
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
                'product' => new ProductForOrderDetailResource($this->product),
                'price' => $this->price,
                'coupon' => new CouponResource($this->coupon),
                'user' => new UserResource($this->user),
                'all_videos_buy' => $this->all_videos_buy,
                'number' => $this->number,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}
