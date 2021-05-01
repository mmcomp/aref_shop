<?php

namespace App\Http\Resources;

use App\Http\Resources\GroupResource;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupGateResource extends JsonResource
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
                'group' => new GroupResource($this->group),
                'key' => $this->key,
                'user' => new UserResource($this->user)
            ];
        }
    }
}
