<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ReportSaleOrderDetailCollection;
use App\Http\Resources\User\PaymentCollection;

class ReportSaleOrderResource extends JsonResource
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
                'saver_user' => [
                   'first_name' => $this->saverUser ? $this->saverUser->first_name : '',
                   'last_name' => $this->saverUser ? $this->saverUser->last_name: ''   
                ],
                'amount' => $this->amount,
                'comment' => $this->comment,
                'order_status' => $this->status,
                'orderDetail' => (new ReportSaleOrderDetailCollection($this->orderDetails)),
                'payments' => (new PaymentCollection($this->payments)),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}
