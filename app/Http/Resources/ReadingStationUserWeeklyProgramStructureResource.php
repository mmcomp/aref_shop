<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationUserWeeklyProgramStructureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        if ($this->resource != null && $this->readingStationUser) {
            $tableNumber = $this->readingStationUser->table_number;
            $package = $this->readingStationUser->package;
            $sluts = $this->readingStationUser->readingStation->sluts->sortBy('start');
            return [
                'tableNumber' => $tableNumber,
                'name' => $package->name,
                'required_time' => $package->required_time,
                'optional_time' => $package->optional_time,
                'sluts' => new ReadingStationSluts2Collection($sluts),
            ];
        }
    }
}
