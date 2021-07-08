<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\ProductOfStudentSessionsResource;

class ProductDetailVideosForShowingToStudentsResource extends JsonResource
{
    protected $foo;

    public function foo($value){
        $this->foo = $value;
        return $this;
    }
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
                'product' => new ProductOfStudentSessionsResource($this->product),
                'is_hidden' => $this->is_hidden,
                'video_session' => new VideoSessionForStudentSessionsResource($this->videoSession),
                'name' => $this->name
            ];
        }
    }
}
