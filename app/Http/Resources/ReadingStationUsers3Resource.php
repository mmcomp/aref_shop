<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationUsers3Resource extends JsonResource
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
                'table_number' => $this->table_number,
                'consultant' => $this->consultant,
                'representative' => $this->representative,
                'contract_start' => $this->contract_start,
                'contract_end' => $this->contract_end,
                'status' => $this->status,
                'readingStation' => new ReadingStation2Resource($this->readingStation),
                'package' => new ReadingStationPackagesResource($this->package),
                'weeklyPrograms' => new ReadingStationWeeklyProgramsCollection($this->weeklyPrograms),
            ];
        }
    }
}
