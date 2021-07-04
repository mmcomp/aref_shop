<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoSessionForStudentSessionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
          'id' => $this->id,
          'start_date' => $this->start_date,
          'start_time' => $this->start_time,
          'end_time' => $this->end_time,
          'video_session_type' => $this->video_session_type,
        ];
    }
}
