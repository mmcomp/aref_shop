<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class VideoSessionsResourceForShowingToStudentsResource extends JsonResource
{
    protected $foo;

    public function foo($value){
        $this->foo = $value;
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
        if ($this->resource != null) {
            return [
                'id' => $this->id,
                'start_date' => $this->start_date,
                'start_time' => date('H:i', strtotime($this->start_time)),
                'end_time' => date('H:i', strtotime($this->end_time)),
                'video_session_type' => $this->video_session_type,
            ];
        }
    }
}
