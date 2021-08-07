<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\RefundResource;


class GetInfoOfAnOrderDetailResource extends JsonResource
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
                'refund' => new RefundResource($this->refund),
                //'coupon' => new CouponResource($this->coupon),
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
