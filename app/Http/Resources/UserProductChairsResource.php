<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductChairsCollection;


class UserProductChairsResource extends JsonResource
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
                'chairs' => new ProductChairsCollection($this->resource['chairs']),
                'reserved_chairs' => $this->resource['reserved_chairs']

            ];
        }
    }
}
