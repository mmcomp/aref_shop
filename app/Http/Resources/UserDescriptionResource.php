<?php

namespace App\Http\Resources;

use App\Models\UserVideoSessionHomework;
use Illuminate\Http\Resources\Json\JsonResource;

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
                "user_video_session_homework" => new UserVideoSessionHomework($this->userVideoSessionHomework),
                "description" => $this->description,
                "user" => $this->user,
                "created_at" => $this->created_at,
                "updated_at" => $this->updated_at
            ];
        }
    }
}
