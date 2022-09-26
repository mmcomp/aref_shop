<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderForShowFactorsResource extends JsonResource
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
                'amount' => $this->amount,
                'comment' => $this->comment,
                'order_status' => $this->status,
                'payments' => (new PaymentCollection($this->payments)),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}
