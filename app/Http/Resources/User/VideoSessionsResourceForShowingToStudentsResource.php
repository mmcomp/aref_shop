<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class VideoSessionsResourceForShowingToStudentsResource extends JsonResource
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
                'start_date' => $this->start_date,
                'start_time' => date('H:i', strtotime($this->start_time)),
                'end_time' => date('H:i', strtotime($this->end_time)),
                'teacher'  => new UserResource($this->teacher),
                'price' => $this->price,
                'video_session_type' => $this->video_session_type,
                'video_link' =>  base64_encode($this->video_link),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}