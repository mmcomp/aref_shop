<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\TeamUserMemmberResource;

class TeamUserWithMemmberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        dd($this);
        return 
        [
            "id" => $this->id,
            "teamName" => $this->name,
            "leaderFullName" => $this->leader->first_name . " " .$this->leader->last_name,
            // "leader" => $this["leader"],
             "memmbers" =>  TeamUserMemmberResource::collection($this->memmbers)
        ];
    }
}
