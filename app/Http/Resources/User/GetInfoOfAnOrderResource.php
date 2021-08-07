<?php

namespace App\Http\Resources\User;

use App\Http\Resources\UserResource;

use Illuminate\Http\Resources\Json\JsonResource;

class GetInfoOfAnOrderResource extends JsonResource
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
                'orderDetail' => (new GetInfoOfAnOrderDetailCollection($this->orderDetails)),
                'payments' => (new PaymentCollection($this->payments)),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}
