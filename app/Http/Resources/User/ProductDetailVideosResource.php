<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\VideoSessionsResource;

class ProductDetailVideosResource extends JsonResource
{
    protected $value;

    public function check($value)
    {
        $this->check = $value;
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
                'videoSession' => (new VideoSessionsResource($this->videoSession))->checkToShowUrlOrNot($this->check)
            ];
        }
    }
}
