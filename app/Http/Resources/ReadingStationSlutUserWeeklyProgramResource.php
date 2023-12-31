<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationSlutUserWeeklyProgramResource extends JsonResource
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
                'package' => new ReadingStationPackagesResource($this->readingStationUser->package),
                'required_time' => $this->required_time,
                'optional_time' => $this->optional_time,
                'required_time_done' => $this->required_time_done,
                'optional_time_done' => $this->optional_time_done,
                'being_point' => $this->being_point,
                'package_point' => $this->package_point,
                'point' => $this->point,
            ];
        }
    }
}
