<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\ResourceCollection;

class QuizzCollection extends ResourceCollection
{
    // protected $foo;

    // public function foo($value){
    //     $this->foo = $value;
    //     return $this;
    // }
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request){
        return $this->collection;
    }
}
