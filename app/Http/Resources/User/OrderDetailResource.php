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
            $productDetails = [];
            $items = [];
            if (isset($this->product)) {
                if (($this->product->type == 'video')) {
                    foreach ($this->orderVideoDetails as $item) {
                        $items[] = $item;
                    }
                    $productDetails = new OrderVideoDetailCollection($items);
                } else if ($this->product->type == 'chairs') {
                    foreach ($this->orderChairDetails as $item) {
                        $items[] = $item;
                    }
                    $productDetails = $items;
                }
            }
            return [
                'id' => $this->id,
                'product' => new ProductForOrderDetailResource($this->product),
                'productDetails' => isset($productDetails) ? $productDetails : null,
                'price' => $this->price,
                'coupons_name' => $this->coupon ? $this->coupon->name : '',
                'coupons_id' => $this->coupons_id,
                'coupons_amount' => $this->coupons_amount,
                'coupons_type' => $this->coupons_type,
                'user' => new UserResource($this->user),
                'all_videos_buy' => $this->all_videos_buy,
                'number' => $this->number,
                'total_price' => $this->total_price,
                'total_price_with_coupon' => $this->total_price_with_coupon,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'orders_id' => $this->orders_id
            ];
        }
    }
}
