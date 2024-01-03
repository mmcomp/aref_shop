<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ReadingStationReadingStaticsReportCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $out = [];
        $data = $this->collection->groupBy('weekly_program_reading_station_user_id');
        foreach ($data as $slutUsers) {
            $cell = $slutUsers[0];
            $readingTotalMinutes = 0;
            $slutUsers->map(function ($userSlut) use (&$readingTotalMinutes) {
                $time = 0;
                switch ($userSlut->status) {
                    case 'late_15':
                        $time = $userSlut->slut->duration - 15;
                        break;
                    case 'late_30':
                        $time = $userSlut->slut->duration - 30;
                        break;
                    case 'late_45':
                        $time = $userSlut->slut->duration - 45;
                        break;
                    case 'late_60':
                        $time = $userSlut->slut->duration - 60;
                        break;
                }
                $readingTotalMinutes += $time;
            });
            $cell->reading_total_minutes = $readingTotalMinutes;
            $out[] = $cell;
        }
        return $out;
    }
}
