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
                'start_date' => $this->start_date,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'teacher' => $this->teacher,
                'product' => $this->product,
                'price' => $this->price,
                'video_session_type' => $this->video_session_type,
                'video_link' => $this->video_link,
                'is_hidden' => $this->is_hidden,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }
    }
}
