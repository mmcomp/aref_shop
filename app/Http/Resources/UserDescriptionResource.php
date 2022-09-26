<?php

namespace App\Http\Resources;

use App\Http\Resources\UserVideoSessionHomeWorkResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class UserDescriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if($this->resource != null) {

            return [
                "user_video_session_homework" => new UserVideoSessionHomeworkResource($this->userVideoSessionHomework),
                "description" => $this->description,
                "user" => new UserResource($this->user),
                "created_at" => $this->created_at,
                "updated_at" => $this->updated_at
            ];
        }
    }
}
