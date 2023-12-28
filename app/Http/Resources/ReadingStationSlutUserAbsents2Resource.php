<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationSlutUserAbsents2Resource extends JsonResource
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
                'day' => $this->day,
                'count' => $this->count,
                'details' => $this->details,
            ];
        }
    }
}
