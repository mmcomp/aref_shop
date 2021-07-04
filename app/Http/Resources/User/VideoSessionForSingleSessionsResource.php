<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoSessionForSingleSessionsResource extends JsonResource
{
    protected $value;

    public function checkToShowUrlOrNot($value)
    {
        $this->checkToShowUrlOrNot = $value;
        return $this;
    }
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
                'videoSessionId' => $this->id,
                'start_date' => $this->start_date,
                'start_time' => date('H:i', strtotime($this->start_time)),
                'end_time' => date('H:i', strtotime($this->end_time)),
                'videoSessionType' => $this->video_session_type,
                'video_link' => $this->checkToShowUrlOrNot ? base64_encode($this->video_link) : null,
            ];
        }
    }
}
