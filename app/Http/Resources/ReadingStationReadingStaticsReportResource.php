<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationReadingStaticsReportResource extends JsonResource
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
                'study' => $this->reading_total_minutes,
            ];
        }
    }
}
