<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CouponResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
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
            $items = [];
            foreach ($this->orderVideoDetails as $item) {                
                $items[] = $item;
            }
            return [
                'id' => $this->id,
                'product' => new ProductForOrderDetailResource($this->product),
                'productDetails' => (new OrderVideoDetailCollection($items)),
                'price' => $this->price,
                'coupon' => new CouponResource($this->coupon),
                'user' => new UserResource($this->user),
                'all_videos_buy' => $this->all_videos_buy,
                'number' => $this->number,
                'total_price' => $this->total_price,
                'total_price_with_coupon' => $this->total_price_with_coupon,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}
