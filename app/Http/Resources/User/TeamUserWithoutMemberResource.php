<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamUserWithoutMemberResource extends JsonResource
{
    // protected $type="salas";
    // public function setType(string $type)
    // {
    //     $this->type=$type;
    // }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    { 
       return  $this->resource  ;   
    }

}
