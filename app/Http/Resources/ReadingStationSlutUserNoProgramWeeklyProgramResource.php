<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationSlutUserNoProgramWeeklyProgramResource extends JsonResource
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
                'point' => -1 * $this->noprogram_point,
                'station' => $this->readingStationUser->readingStation->name,
            ];
        }
    }
}
