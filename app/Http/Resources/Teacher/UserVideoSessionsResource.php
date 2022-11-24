<?php

namespace App\Http\Resources\Teacher;

use Illuminate\Http\Resources\Json\JsonResource;

class UserVideoSessionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->resource != null) {
            return [
                'id' => $this->id,
                'users_id' => $this->users_id,
                'video_sessions_id' => $this->video_sessions_id, // date('H:i', strtotime($this->start_time)),

            ];
        }
    }
}
