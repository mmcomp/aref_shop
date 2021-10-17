<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserVideoSessionPresentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if($this->resource != null) {
            return [
               'id' => $this->id,
               'user' => new UserWithFirstNameLastNameEmailResource($this->user),
               'offline_spend' => $this->offline_spend,
               'offline_started_at' => $this->offline_started_at,
               'offline_exited_at' => $this->offline_exited_at,
               'online_spend' => $this->online_spend,
               'online_started_at' => $this->online_started_at,
               'online_exited_at' => $this->online_exited_at,
               'created_at' => $this->created_at,
               'updated_at' => $this->updated_at
            ];
        }
    }
}
