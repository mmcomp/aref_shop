<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
              'id' => $this->id,
              'title' => $this->title,
              'published' => $this->published,
              'date' => $this->date,
              'content' => $this->content,
              'created_at' => $this->created_at,
              'updated_at' => $this->updated_at
            ];
        }
    }
}
