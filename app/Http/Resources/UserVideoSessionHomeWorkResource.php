<?php

namespace App\Http\Resources;

use App\Models\UserVideoSession;
use Illuminate\Http\Resources\Json\JsonResource;

class UserVideoSessionHomeWorkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //dd($this->resource);
        if($this->resource != null){
            return [
                'id' => $this->id,
                'user_video_session' => $this->userVideoSession,
                'file' => $this->file,
                'teacher_description' => $this->teacher_description,
                'description' => $this->description,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}
