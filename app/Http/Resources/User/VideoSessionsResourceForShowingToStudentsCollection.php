<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VideoSessionsResourceForShowingToStudentsCollection extends ResourceCollection
{
    protected $foo;

    public function foo($value){
        $this->foo = $value;
        return $this;
    }
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request){
        return $this->collection->map(function (VideoSessionsResourceForShowingToStudentsResource $resource) use ($request) {
            return $resource->foo($this->foo)->toArray($request);
        })->filter()->all();
    }
}
