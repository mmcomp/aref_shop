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
                'email' => $this->email,
                'first_name' => $this->first_name == null ? "" : $this->first_name,
                'last_name' => $this->last_name,
                'avatar_path' => $this->avatar_path,
                'referrer_user' => new UserResource($this->referrerUser),
                'saver_users_id' => [
                   'first_name' => $this->saverUser ? $this->saverUser->first_name : '',
                   'last_name' => $this->saverUser ? $this->saverUser->last_name : ''
                ],
                'address' => $this->address,
                'postall' => $this->postall,
                'city' => new CityResource($this->city),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'group' => new GroupResource($this->group),
            ];
        }
    }
}
