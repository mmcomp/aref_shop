<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
            $startOfThisWeek = Carbon::now()->startOfWeek(Carbon::SATURDAY)->toDateString();
            $endOfThisWeek = Carbon::now()->endOfWeek(Carbon::FRIDAY)->toDateString();
            $startOfNextWeek = Carbon::now()->startOfWeek(Carbon::SATURDAY)->addDays(7)->toDateString();
            $endOfNextWeek = Carbon::now()->endOfWeek(Carbon::FRIDAY)->addDays(7)->toDateString();            
            $tableNumber = $this->readingStationUser->table_number;
            $package = $this->readingStationUser->package;
            $sluts = $this->readingStationUser->readingStation->sluts->sortBy('start');
            $weeklyPrograms = $this->readingStationUser->weeklyPrograms->map(function ($weeklyProgram) {
                $weeklyProgram->sluts;
                return $weeklyProgram;
            });
            return [
                'tableNumber' => $tableNumber,
                'name' => $package->name,
                'required_time' => $package->required_time,
                'optional_time' => $package->optional_time,
                'sluts' => new ReadingStationSluts2Collection($sluts),
                'weeklyPrograms' => new ReadingStationWeeklyPrograms2Collection($weeklyPrograms),
                'startOfThisWeek' => $startOfThisWeek,
                'endOfThisWeek' => $endOfThisWeek,
                'startOfNextWeek' => $startOfNextWeek,
                'endOfNextWeek' => $endOfNextWeek,
            ];
        }
    }
}
