<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailVideosResource extends JsonResource
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
                'name' => $this->name,
                'product' => $this->product,
                'price' => $this->price,
                'video_sessions_id' => $this->videoSession,
                'extraordinary' => $this->extraordinary,
                'is_hidden' => $this->is_hidden,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }
    }
}
