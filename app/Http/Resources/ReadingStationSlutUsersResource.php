<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationSlutUsersResource extends JsonResource
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
                'weeklyProgram' => new ReadingStationWeeklyProgramsResource($this->weeklyProgram),
                'slut' => new ReadingStationSlutsResource($this->slut),
                'day' => $this->day,
                'is_required' => $this->is_required,
                'end' => $this->end,
                'absenseReason' => new ReadingStationAbsentReasonResource($this->absenseReason),
                'reading_station_absent_reason_score' => $this->reading_station_absent_reason_score,
                'absense_approved_status' => $this->absense_approved_status,
            ];
        }
    }
}
