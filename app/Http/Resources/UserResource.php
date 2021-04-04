<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        if($this->resource != null){
            return [
                'id' => $this->id,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'avatar_path' => $this->avatar_path,
                'referrer_users_id' => $this->referrer_users_id,
                'address' => $this->address,
                'postall' => $this->postall,
                'cities_id' => ($this->city) ? $this->city->name : '-',
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'groups_id' => ($this->group) ? $this->group->name : '-',
            ];
        }
    }
}
