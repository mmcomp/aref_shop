<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\VideoSessionsResource;

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
        $files = [];
       
        if ($this->resource != null) {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'price' => $this->price,
                'video_session' => new VideoSessionsResource($this->videoSession),
                'extraordinary' => $this->extraordinary,
                'is_hidden' => $this->is_hidden,
                'single_purchase' => $this->single_purchase,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }
    }
}
