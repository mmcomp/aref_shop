<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationSlutChangeWarningResource extends JsonResource
{
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
                'description' => $this->description,
                'is_read' => $this->is_read ? true : false,
                'operator' => new UserResource($this->operator),
                'reader' => new UserResource($this->reader),
            ];
        }
    }
}
