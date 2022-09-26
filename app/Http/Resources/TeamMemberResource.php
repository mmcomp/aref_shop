<?php

namespace App\Http\Resources;
use App\Http\Resources\TeamUserMemberCollection;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {               
        return [           
            "id" => $this->id,
            "team_user_id" => $this->team_user_id,
            "mobile" =>$this->mobile,
            "is_verified" => $this->is_verified,
            "member" =>(new TeamUserMemberResource($this->member))           
        ];
    }
}
