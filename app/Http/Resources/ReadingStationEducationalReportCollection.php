<?php

namespace App\Http\Resources;

use App\Utils\CollectionPaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReadingStationEducationalReportCollection extends ResourceCollection
{
    private $perPage;
    private $pageNumber;
    private $sort;
    private $sortDir;
    function __construct($resource, $perPage = null, $pageNumber = null, $sort = null, $sortDir = null)
    {
        $this->perPage = $perPage;
        $this->pageNumber = $pageNumber;
        $this->sort = $sort;
        $this->sortDir = $sortDir;
        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $out = collect([]);
        $data = $this->collection->groupBy('weekly_program_reading_station_user_id');
        $total = 0;
        foreach ($data as $slutUsers) {
            $cell = $slutUsers[0];
            $cell->absent_not_approved_count = $slutUsers
                ->where('status', 'absent')
                ->where('absense_approved_status', 'not_approved')
                ->count();
            $cell->absent_semi_approved_count = $slutUsers
                ->where('status', 'absent')
                ->where('absense_approved_status', 'semi_approved')
                ->count();
            $cell->absent_approved_count = $slutUsers
                ->where('status', 'absent')
                ->where('absense_approved_status', 'approved')
                ->count();
            $cell->not_plus_60_late_count = $slutUsers
                ->where('status', 'like', 'late_%')
                ->where('status', '!=', 'late_60_plus')
                ->count();
            $cell->plus_60_late_count = $slutUsers
                ->where('status', 'late_60_plus')
                ->count();
            $point = -2 * ($cell->plus_60_late_count + $cell->absent_not_approved_count) - $cell->absent_semi_approved_count;
            $readingTotalMinutes = 0;
            $slutUsers->map(function ($userSlut) use (&$readingTotalMinutes) {
                $time = 0;
                switch ($userSlut->status) {
                    case 'late_15':
                        $time = $userSlut->slut->duration - 15;
                        break;
                    case 'late_30':
                        $time = $userSlut->slut->duration - 30;
                        break;
                    case 'late_45':
                        $time = $userSlut->slut->duration - 45;
                        break;
                    case 'late_60':
                        $time = $userSlut->slut->duration - 60;
                        break;
                    case 'present':
                        $time = $userSlut->slut->duration;
                        break;

                }
                $readingTotalMinutes += $time;
            });
            $cell->reading_total_minutes = $readingTotalMinutes;
            $cell->point = $point;
            $cell->table_number = $cell->weeklyProgram->readingStationUser->table_number;
            $total += $point;
            $out[] = $cell;
        }

        if ($this->sort) {
            if ($this->sortDir && strtolower($this->sortDir) === 'desc') {
                $out = $out->sortByDesc($this->sort);
            } else {
                $out = $out->sortBy($this->sort);
            }
        }
        return new CollectionPaginator($out->forPage($this->pageNumber,$this->perPage), count($out),$this->perPage, $total, $this->pageNumber);
    }
}
