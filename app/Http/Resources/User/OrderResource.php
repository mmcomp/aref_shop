<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CouponResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
                'user' => new UserResource($this->user),
                'amount' => $this->amount,
                'comment' => $this->comment,
                'order_status' => $this->status,
                // 'order_created_at' => $this->created_at,
                // 'order_updated_at' => $this->updated_at,
                'product' => $this->orderDetail ? new ProductResource($this->orderDetail->product) : null,
                'price' => $this->orderDetail ? $this->orderDetail->price : null,
                'coupon' => $this->orderDetail ? new CouponResource($this->orderDetail->coupon) : null,
                'all_videos_buy' => $this->orderDetail ? $this->orderDetail->all_videos_buy : null,
                'order_detail_status' => $this->orderDetail ? $this->orderDetail->status : null,
                'number' => $this->orderDetail ? $this->orderDetail->number : null
                // 'order_detail_created_at' => $this->orderDetail ? $this->orderDetail->created_at : null,
                // 'order_detail_updated_at' => $this->orderDetail ? $this->orderDetail->updated_at : null,
            ];
        }
    }
}
