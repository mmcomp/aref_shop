<?php

namespace App\Http\Resources\Server;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ListOfVideosOfAProductCollection extends ResourceCollection
{
    protected $value;
    public function foo($value)
    {
        $this->foo = $value;
        return $this;
    }
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'product_detail_videos' => parent::toArray($request),
            'product' => [
                'name' => $this->foo["name"],
                'thumbnail' => $this->foo["thumbnail"]
            ]
        ];
    }
}
