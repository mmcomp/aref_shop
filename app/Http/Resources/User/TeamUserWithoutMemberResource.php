<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamUserWithoutMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {   
       return  $this->resource  ;
       if($this->resource!==null)
       {
            return 
            [
                "id"=>$this->id,
                "user_id_creator" => $this->user_id_creator
            ]; 
       }         
       // [
            // "teamName" => $this->resource["team_user"]["name"] ,           
            // "leaderFullName" => $this->resource ,     
          
       // ];
    }
}