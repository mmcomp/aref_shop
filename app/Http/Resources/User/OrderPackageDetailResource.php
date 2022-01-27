<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderPackageDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->resource != null) {
            return [
                "id" => $this->id,
                "order_details_id" => $this->order_details_id,
                "product_child_id" =>$this->product_child_id
            ];
        }
    }
}
