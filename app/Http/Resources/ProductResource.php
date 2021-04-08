<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
                'name' => $this->name,
                'short_description' => $this->short_description,
                'long_description' => $this->long_description,
                'price' => $this->price,
                'sale_price' => $this->sale_price,
                'sale_expire' => $this->sale_expire,
                'video_props' => $this->video_props,
                'category_one' => $this->category_ones,
                'category_two' => $this->category_twos,
                'category_three' => $this->category_threes,
                'category_four' => $this->category_fours,
                'main_image_path' => $this->main_image_path,
                'main_image_thumb_path' => $this->main_image_thumb_path,
                'second_image_path' => $this->second_image_path,
                'published' => $this->published,
                'type' => $this->type
            ];
        }
    }
}
