<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationEducationalReportResource extends JsonResource
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
                'table_number' => $this->weeklyProgram->readingStationUser->table_number,
                'first_name' => $this->weeklyProgram->readingStationUser->user->first_name,
                'last_name' => $this->weeklyProgram->readingStationUser->user->last_name,
                'absent_not_approved_count' => $this->absent_not_approved_count,
                'absent_semi_approved_count' => $this->absent_semi_approved_count,
                'absent_approved_count' => $this->absent_approved_count,
                'not_plus_60_late_count' => $this->not_plus_60_late_count,
                'plus_60_late_count' => $this->plus_60_late_count,
                'reading_total_minutes' => $this->reading_total_minutes,
                'point' => $this->point,
            ];
        }
    }
}
