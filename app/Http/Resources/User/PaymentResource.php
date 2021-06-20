<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
                'orders_id' => $this->orders_id,
                'users_id' => $this->users_id,
                'price' => $this->price,
                'sale_reference_id' => $this->sale_reference_id,
                'sale_order_id' => $this->sale_order_id,
                'res_code' => $this->res_code,
                'ref_id' => $this->ref_id,
                'bank_returned' => $this->bank_returned,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}
