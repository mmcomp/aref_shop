<?php

namespace App\Http\Resources\User;

use App\Http\Resources\UserResource;
use App\Http\Resources\User\OrderDetailCollection;
use App\Http\Resources\User\OrderVideoDetailCollection;
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
        dd($this->numArray);
        if ($this->resource != null) {
            return [
                'id' => $this->id,
                'user' => new UserResource($this->user),
                'amount' => $this->amount,
                'comment' => $this->comment,
                'order_status' => $this->status,
                'orderDetail' => new OrderDetailCollection($this->orderDetails),
                'numArray' => $this->numArray
            ];
        }
    }
}
