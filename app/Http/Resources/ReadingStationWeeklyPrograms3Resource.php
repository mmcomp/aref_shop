<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationWeeklyPrograms3Resource extends JsonResource
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
                'start' => $this->start,
                'end' => $this->end,
                'readingStationUser' => new ReadingStationUsers5Resource($this->readingStationUser),
            ];
        }
    }
}
