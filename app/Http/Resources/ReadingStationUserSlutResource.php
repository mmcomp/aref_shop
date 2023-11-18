<?php

namespace App\Http\Resources;

use App\Models\ReadingStationUser;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationUserSlutResource extends JsonResource
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
                'day' => $this->day,
                'is_required' => $this->is_required,
                'status' => $this->status,
                'absenseReason' => new ReadingStationAbsentReasonsResource($this->absenseReason),
                'reading_station_absent_reason_score' => $this->reading_station_absent_reason_score,
                'absense_approved_status' => $this->absense_approved_status,
                'absentPresent' => new ReadingStationAbsentPresentResource( $this->absentPresent),
            ];
        }
    }
}
