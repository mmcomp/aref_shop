<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if($this->resource !== null)
        {
            return 
        [
            "id" =>$this->id,
            "user_id_leader" => $this->user_id_creator,
            "name" => $this->name,
            "if_full" => $this->is_full
        ];
        }
        
    }
}
