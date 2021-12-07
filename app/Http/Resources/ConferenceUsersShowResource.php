<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConferenceUsersShowResource extends JsonResource
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
            "email" => $this->email,
            "first_name" => $this->first_name,
            "last_name" =>  $this->last_name,
            "referrer" => $this->referrer, 
            "product_detail_videos_id" => $this->product_detail_videos_id,
            //"name" => $this->name,
            "already_registerd"=>$this->already_registerd        
        ];
    }
}
