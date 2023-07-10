<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoSessionsResource extends JsonResource
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
                'start_date' => $this->start_date,
                'start_time' => date('H:i', strtotime($this->start_time)),
                'end_time' => date('H:i', strtotime($this->end_time)),
                'teacher'  => new UserResource($this->teacher),
                'price' => $this->price,
                'show_in_zero_price' => $this->show_in_zero_price,
                'video_session_type' => $this->video_session_type,
                'video_link' => $this->video_link,
                'is_aparat' => $this->is_aparat,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}
