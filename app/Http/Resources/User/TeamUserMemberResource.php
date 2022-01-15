<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\TeamUserMemberGetUserDetailsResource;


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
        if($this->resource != null){
            return 
            [
                //"id" => $this->id,
                //"team_user_id" => $this->team_user_id,
                "mobile" =>$this->mobile,
                "is_verified" =>$this->is_verified,
                "memberFullName"  =>  new TeamUserMemberGetUserDetailsResource($this->member)
            ];
        }
    }
}
