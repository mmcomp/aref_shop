<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationUserStrikesResource extends JsonResource
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
                'first_name' => $this->readingStationSlutUser->weeklyProgram->readingStationUser->user->first_name,
                'last_name' => $this->readingStationSlutUser->weeklyProgram->readingStationUser->user->last_name,
                'table_number' => $this->readingStationSlutUser->weeklyProgram->readingStationUser->table_number,
                'strike' => new ReadingStationStrikesResource($this->readingStationStrike),
                'point' => (isset($this->readingStationStrike) && $this->readingStationStrike->is_point ? 1 : -1) * $this->reading_station_strike_score,
                'description' => $this->description,
                'slut' => [
                    "id" => $this->readingStationSlutUser->slut->id,
                    "name" => $this->readingStationSlutUser->slut->name,
                ],
                "day" => $this->day,
                "station" => $this->readingStationSlutUser->weeklyProgram->readingStationUser->readingStation->name,
            ];
        }
    }
}
