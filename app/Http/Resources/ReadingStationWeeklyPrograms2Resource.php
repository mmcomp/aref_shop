<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationWeeklyProgramsResource extends JsonResource
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
                'name' => $this->name,
                'required_time'=> $this->required_time,
                'optional_time'=> $this->optional_time,
                'is_verified'=> $this->is_verified,
                'start'=> $this->start,
                'end'=> $this->end,
                'required_time_done'=> $this->required_time_done,
                'optional_time_done'=> $this->optional_time_done,
                'strikes_done'=> $this->strikes_done,
                'absence_done'=> $this->absence_done,
                'sluts' => new ReadingStationSlutUsersCollection($this->sluts),
            ];
        }
    }
}
