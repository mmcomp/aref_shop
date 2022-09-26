<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\TeamMemberCollection;

class ShowAllTeamResource extends JsonResource
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
            "user_id_creator" =>$this->user_id_creator,
            "name" =>$this->name,
            "is_full" => $this->is_full,
            "created_at" => $this->created_at,
            // "team_member" =>(new TeamMemberCollection($this->team_member)),
            "team_member" =>(new TeamMemberCollection($this->TeamMember)),
            "leader" => $this->leader
        ];
    }
}
