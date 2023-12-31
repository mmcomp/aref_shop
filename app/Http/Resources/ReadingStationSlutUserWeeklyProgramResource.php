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
            $score = 0;
            if ($this->required_time_done + $this->optional_time_done < $this->required_time + $this->optional_time) {
                $score = -2;
            } else if ($this->required_time_done + $this->optional_time_done > $this->required_time + $this->optional_time) {
                $diff = ($this->required_time_done + $this->optional_time_done) - ($this->required_time + $this->optional_time);
                $step = $this->readingStationUser->package->step ?? 10;
                $score = (($diff - ($diff % $step)) / $step) + 1 ;
            }
            return [
                'id' => $this->id,
                'start' => $this->start,
                'end' => $this->end,
                'package' => new ReadingStationPackagesResource($this->readingStationUser->package),
                'required_time' => $this->required_time,
                'optional_time' => $this->optional_time,
                'required_time_done' => $this->required_time_done,
                'optional_time_done' => $this->optional_time_done,
                'score' => $score,
            ];
        }
    }
}
