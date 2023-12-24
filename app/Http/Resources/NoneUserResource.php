<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class NoneUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        if ($this->resource != null) {

            return [
                'id' => $this->id,
                'email' => $this->email,
                'first_name' => $this->first_name == null ? "" : $this->first_name,
                'last_name' => $this->last_name,
                'created_at' => $this->created_at,
                'group' => new GroupResource($this->group),
                'disabled' => $this->disabled ? true : false,
            ];
        }
    }
}
