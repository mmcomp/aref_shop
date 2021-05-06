<?php

namespace App\Http\Resources;

use App\Http\Resources\FileResource;
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
        $files = [];
        if($this->resource != null){
            if($this->product_files){
                foreach($this->product_files as $file){
                   if($file->file != null){
                        $files[] = new FileResource($file->file);
                   }
                }
            }
            return [
                'id' => $this->id,
                'name' => $this->name,
                'short_description' => $this->short_description,
                'long_description' => $this->long_description,
                'price' => $this->price,
                'sale_price' => $this->sale_price,
                'sale_expire' => $this->sale_expire,
                'video_props' => $this->video_props,
                'category_one' => new CategoryOnesResource($this->category_ones),
                'category_two' => new CategoryTwosResource($this->category_twos),
                'category_three' => new CategoryThreesResource($this->category_threes),
                'main_image_path' => $this->main_image_path,
                'main_image_thumb_path' => $this->main_image_thumb_path,
                'second_image_path' => $this->second_image_path,
                'files' => $files,
                'published' => $this->published,
                'type' => $this->type,
                'special' => $this->special,
                'education_system' => $this->education_system,
                'hour' => $this->hour,
                'days' => $this->days,
                'start_date' => $this->start_date,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}
