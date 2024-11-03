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
            $toDo = $this->required_time + $this->optional_time;
            $done =  $this->required_time_done + $this->optional_time_done;
            if ($done < $toDo) {
                $point = -2;
            } else if ($this->required_time_done >= $this->required_time) {
                $step = ($this->readingStationUser->package->step ?? 10) * 60;
                $extra = $done - $toDo;
                if ($extra > 0) {
                    $point = intval($extra/ $step) * 2;
                }
            }
            return [
                'id' => $this->id,
                'start' => $this->start,
                'end' => $this->end,
                'package_name' => $this->name,
                'minimum_reading_minutes' => $toDo,
                'reading_done_minutes' => $done,
                'point' => $point,
                'station' => $this->readingStationUser->readingStation->name,
            ];
        }
    }
}
