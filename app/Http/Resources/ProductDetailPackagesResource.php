<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailPackagesResource extends JsonResource
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
                'child_products_id' => $this->childproduct ? $this->childproduct->name : '-',
            ];
        }
    }
}
