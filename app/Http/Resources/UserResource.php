<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;
use App\Models\City;

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
                'email' => $this->email,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'avatar_path' => $this->avatar_path,
                'referrer_users_id' => User::select('id','first_name','last_name')->where('is_deleted',false)->where('id',$this->referrer_users_id)->first(),
                'address' => $this->address,
                'postall' => $this->postall,
                'cities_id' => City::select('id','name')->where('is_deleted',false)->where('id',$this->cities_id)->first(),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'groups_id' => ($this->group) ? $this->group->name : null,
            ];
        }
    }
}
