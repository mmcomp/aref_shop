<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\TeamUserMemmberGetUserDetailsResource;


class TeamUserMemmberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //dd($this->member->toArray()["first_name"]);
        //$tmp=$this->member->toArray(); 
        //dd($tmp["first_name"]);      
        //$fullname= $tmp["first_name"];
        if($this->resource != null){
            return 
            [
                //"id" => $this->id,
                //"team_user_id" => $this->team_user_id,
                "mobile" =>$this->mobile,
                "is_verified" =>$this->is_verified,
                "memmberFullName"  =>  new TeamUserMemmberGetUserDetailsResource($this->member)
            ];
        }
    }
}
