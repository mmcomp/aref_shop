<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\TeamUserMemberResource;

class TeamUserWithMemberResource extends JsonResource
{
    // protected $type;
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
        return $this->resource;
        
        // return  $this->resource;  
        // if($this->resource !==null)
        // {
            // return 
            //     [
            //         "id" => $this->id,
            //         "teamName" => $this->name,
            //         "leaderFullName" => $this->leader->first_name . " " .$this->leader->last_name,
            //         // "leader" => $this["leader"],
            //         "members" =>  TeamUserMemberResource::collection(($this->members))
            //     ];
        // }
        // return null;
        
    }
}
