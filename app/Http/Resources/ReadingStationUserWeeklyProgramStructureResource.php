<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationUserWeeklyProgramStructureResource extends JsonResource
{
    private $weeklyPrograms;
    function __construct($resource, $weeklyPrograms = null)
    {
        $this->weeklyPrograms = $weeklyPrograms;
        parent::__construct($resource);
    }

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
            $weeklyPrograms = collect($this->weeklyPrograms ?? $this->readingStationUser->noneZeroSlutWeeklyPrograms)->sortBy('start');
            if (count($weeklyPrograms) === 1 && Carbon::parse($weeklyPrograms->first()->start)->gt(Carbon::now())) {
                $weeklyPrograms = [null, $weeklyPrograms->first()];
            }
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
                'first_name' => $this->readingStationUser->user->first_name,
                'last_name' => $this->readingStationUser->user->last_name,
            ];
        }
    }
}
