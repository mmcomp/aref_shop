<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\FileResource;
use App\Http\Resources\User\VideoSessionsResource;

class ProductDetailVideosResourceForConference extends JsonResource
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
                'free_conference_description' => $this->free_conference_description,
                'free_conference_start_mode' => $this->free_conference_start_mode,
                'name' => $this->name,
                'products_id' => $this->products_id
            ];
        }
    }
}
