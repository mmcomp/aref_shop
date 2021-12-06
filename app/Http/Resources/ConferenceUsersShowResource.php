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
            "mobile" => $this->email,
            "firstName" => $this->first_name,
            "lastName" =>  $this->last_name,
            "referrer" => $this->referrer, 
            "productDetailVideosId" => $this->product_detail_videos_id,
            "name" => $this->name,
            "alreadyRegistered"=>$this->already_registerd        
        ];
    }
}
