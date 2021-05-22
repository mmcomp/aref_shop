<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductFileResource extends JsonResource
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
                'id' => ($this->file) ? $this->file->id : null,
                'name' => ($this->file) ? $this->file->name : '',
                'description' => ($this->file) ? $this->file->description : null,
                'user' => ($this->file) ? new UserResource($this->file->user) : 0,
                'product' => new ProductResource($this->product),
                'file_path' => ($this->file) ? $this->file->file_path : null
            ];
        }
    }
}
