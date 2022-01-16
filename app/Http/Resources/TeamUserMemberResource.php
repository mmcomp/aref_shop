<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamUserMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {   
         //return $this->resource;  
         if($this->resource!==null)      
         {
            return [           
                "id" => $this->id,
                "email" => $this->email,
                "first_name" =>$this->first_name,
                "last_name" => $this->last_name,               
            ];
         }
        
    }
}
