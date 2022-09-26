<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RefundResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if($this->resource != null) {
            return [
               "orders_id" => $this->orders_id,
               "products_id" => $this->products_id,
               "product_detail_videos_id" => $this->product_detail_videos_id,
               "user" => new UserRefundResource($this->saverUser),
               "description" => $this->description,
               "created_at" => $this->created_at,
               "updated_at" => $this->updated_at
            ];
        }
    }
}
