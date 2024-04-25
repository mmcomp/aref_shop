<?php

namespace App\Http\Resources;

use App\Models\ReadingStationSlutChangeWarning;
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
                // 'absentPresent' => new ReadingStationAbsentPresentResource( $this->absentPresent),
                // 'warnings' => new ReadingStationSlutChangeWarningCollection($this->unReadWarnings),
            ];
        }
    }
}
