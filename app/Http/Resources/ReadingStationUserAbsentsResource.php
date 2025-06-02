<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationUserAbsentsResource extends JsonResource
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
                'first_name' => $this->weeklyProgram->readingStationUser->user->first_name,
                'last_name' => $this->weeklyProgram->readingStationUser->user->last_name,
                'table_number' => $this->weeklyProgram->readingStationUser->table_number,
                'slut_name' => $this->slut->name,
                'slut_start' => $this->slut->start,
                'slut_end' => $this->slut->end,
                'groupId' => $this->groupId,
                'score' => 2,
                'status' => $this->absense_approved_status,
            ];
        }
    }
}
