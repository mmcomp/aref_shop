<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationSlutUserAvailableWeeklyProgramResource extends JsonResource
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
            $point = 0;
            $toDo = $this->readingStationUser->package->required_time + $this->readingStationUser->package->optional_time;
            $done =  $this->required_time_done + $this->optional_time_done;
            if ($done < $toDo) {
                $point = -2;
            } else {
                $step = $this->readingStationUser->package->step ?? 10;
                $point = ($done - ($done % $step)) * 2 / $step;
            }
            return [
                'id' => $this->id,
                'start' => $this->start,
                'end' => $this->end,
                'package_name' => $this->readingStationUser->package->name,
                'minimum_reading_minutes' => $toDo,
                'reading_done_minutes' => $done,
                'point' => $point,
            ];
        }
    }
}
