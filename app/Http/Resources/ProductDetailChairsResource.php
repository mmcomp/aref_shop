<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailChairsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if($this->resource != null){
            return [
                'id' => $this->id,
                'products_id' => $this->product ? $this->product->name : '-',
                'start' => $this->start,
                'end' => $this->end,
                'price' => $this->price,
                'description' => $this->description
            ];
        }
    }
}
