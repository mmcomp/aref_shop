<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationStrikeReportResource extends JsonResource
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
                'table_number' => $this->readingStationSlutUser->weeklyProgram->readingStationUser->table_number,
                'first_name' => $this->readingStationSlutUser->weeklyProgram->readingStationUser->user->first_name,
                'last_name' => $this->readingStationSlutUser->weeklyProgram->readingStationUser->user->last_name,
                'day' => $this->day,
                'slut' => new ReadingStationSluts2Resource($this->readingStationSlutUser->slut),
                'description' => new ReadingStationStrikesResource($this->readingStationStrike),
            ];
        }
    }
}
