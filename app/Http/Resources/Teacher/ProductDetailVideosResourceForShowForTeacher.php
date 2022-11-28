<?php

namespace App\Http\Resources\Teacher;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\FileResource;
use App\Http\Resources\User\VideoSessionsResource;

class ProductDetailVideosResourceForShowForTeacher extends JsonResource
{
    protected $value;

    public function check($value)
    {
        $this->check = $value;
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
        return $this->resource;
        $files = [];
        if ($this->resource != null) {
            // if ($this->videoSession) {
            //     foreach ($this->videoSession->videoSessionFiles as $file) {
            //         if ($file->file != null) {
            //             $files[] = new FileResource($file->file);
            //         }
            //     }
            // }
            return [
                'id' => $this->id,
                'name' => $this->name,
                'product' => $this->product,
                'price' => $this->price,
                'files' => $files,
                'video_session' => (new VideoSessionsResource($this->videoSession))->checkToShowUrlOrNot($this->check),
                'extraordinary' => $this->extraordinary,
                'is_hidden' => $this->is_hidden,
                'single_purchase' => $this->single_purchase,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'free_conference_before_start_text' => $this->free_conference_before_start_text,
                'video_for_teacher' => (new UserVideoSessionsResource($this->videoSession))
            ];
        }
    }
}
