<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationSlutUsers2Resource extends JsonResource
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
                'status' => $this->status,
                'reading_station_slut_id' => $this->reading_station_slut_id,
                'day' => $this->day,
                'is_required' => $this->is_required,
                'end' => $this->end,
                'absenseReason' => new ReadingStationAbsentReasonsResource($this->absenseReason),
                'reading_station_absent_reason_score' => $this->reading_station_absent_reason_score,
                'absense_approved_status' => $this->absense_approved_status,
                // 'absentPresent' => new ReadingStationAbsentPresent2Resource($this->absentPresent),
                'calls' => new ReadingStationCallsCollection($this->calls),
                'warnings' => new ReadingStationSlutChangeWarningCollection($this->unReadWarnings),
            ];
        }
    }
}
