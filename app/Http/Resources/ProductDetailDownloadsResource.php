<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailDownloadsResource extends JsonResource
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
                'products_id' => new ProductResource($this->product),
                'file_path' => $this->file_path,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}
