<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationWeeklyProgramHoursResource extends JsonResource
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
            $sluts = $this->sluts->whereNotIn('status', ['defined', 'absent', 'sleep']);
            $result = [
                "Saturday" => 0,
                "Sunday" => 0,
                "Monday" => 0,
                "Tuesday" => 0,
                "Wednesday" => 0,
                "Thursday" => 0,
                "Friday" => 0,
            ];
            foreach ($sluts as $slut) {
                $time = $slut->slut->duration;
                switch ($slut->status) {
                    case 'late_15':
                        $time -= 15;
                        break;
                    case 'late_30':
                        $time -= 30;
                        break;
                    case 'late_45':
                        $time -= 45;
                        break;
                    case 'late_60':
                        $time -= 60;
                        break;
                    case 'late_60_plus':
                        $time = 0;
                        break;
                }
                $result[Carbon::parse($slut->day)->format('l')] += $time;
            }
            return $result;
        }
    }
}
