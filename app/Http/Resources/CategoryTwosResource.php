<?php

namespace App\Http\Resources;

use App\Http\Resources\CategoryOnesResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryTwosResource extends JsonResource
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
                'category_one' => new CategoryOnesResource($this->category_one),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}
