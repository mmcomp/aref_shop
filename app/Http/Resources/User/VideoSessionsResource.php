<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoSessionsResource extends JsonResource
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
        //dd($this->checkToShowUrlOrNot);
        if($this->resource != null){
            return [
                // 'id' => $this->id,
                // 'start_date' => $this->start_date,
                // 'start_time' => date('H:i', strtotime($this->start_time)),
                // 'end_time' => date('H:i', strtotime($this->end_time)),
                //'teacher'  => new UserResource($this->teacher),
                // 'price' => $this->price,
                // 'video_session_type' => $this->video_session_type,
                'video_link' => $this->checkToShowUrlOrNot ? base64_encode($this->video_link) : null,
                // 'created_at' => $this->created_at,
                // 'updated_at' => $this->updated_at
            ];
        }
    }
}
