<?php

namespace App\Http\Resources\User;
use Carbon\Carbon;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoSessionsResource extends JsonResource
{
    protected $value;
    protected $skyRoomUrl;

    public function checkToShowUrlOrNot($value, $skyRoomUrl)
    {
        $this->checkToShowUrlOrNot = $value;
        $this->skyRoomUrl = $skyRoomUrl;
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
        $videoLink = $this->checkToShowUrlOrNot ? base64_encode($this->video_link) : null;
        if ($this->is_sky_room) {
            if ($this->skyRoomUrl != "" && $this->price > 0) {
                $videoLink = $this->skyRoomUrl;
            } else {
                $videoLink = $this->video_link;
            }
        }
        if ($this->resource != null) {
            return [
                'id' => $this->id,
                'start_date' => $this->start_date,
                'start_time' => date('H:i', strtotime($this->start_time)),
                'end_time' => date('H:i', strtotime($this->end_time)),
                //'teacher'  => new UserResource($this->teacher),
                // 'price' => $this->price,
                'video_session_type' => $this->video_session_type,
                'video_link' => $videoLink,
                "is_aparat" => $this->is_aparat,
                "is_sky_room" => $this->is_sky_room,
                'currentTime' => Carbon::now()->format('H:i:s')
                // 'created_at' => $this->created_at,
                // 'updated_at' => $this->updated_at
            ];
        }
    }
}
