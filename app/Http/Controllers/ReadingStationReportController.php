<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationEducationalReportRequest;
use App\Http\Resources\ReadingStationEducationalReportCollection;
use App\Models\ReadingStation;
use App\Models\ReadingStationSlutUser;
use Illuminate\Support\Facades\DB;

class ReadingStationReportController extends Controller
{
    public function educational(ReadingStationEducationalReportRequest $request, ReadingStation $readingStation)
    {
        $slutUsers = ReadingStationSlutUser::whereHas('weeklyProgram', function ($q1) use ($readingStation, $request) {
            $q1->whereHas('readingStationUser', function ($q2) use ($readingStation, $request) {
                $q2->where('reading_station_id', $readingStation->id);
                if ($request->exists('table_number')) {
                    $q2->where('table_number', $request->table_number);
                }
                if ($request->exists('name')) {
                    $q2->whereHas('user', function ($q3) use ($request) {
                        $q3->where(DB::raw("CONCAT(IFNULL(first_name, ''), IFNULL(CONCAT(' ', last_name), ''))"), 'like', '%' . $request->name . '%');
                    });
                }
            });
        });
        $slutUsers->whereDate('day', '>=', $request->from_date);
        $slutUsers->whereDate('day', '<=', $request->to_date);
        $slutUsers->withAggregate('weeklyProgram', 'reading_station_user_id');
        $perPage = $request->per_page ?? env('PAGE_COUNT');
        $page = $request->page ?? 1;
        $data = $slutUsers->get();

        return (new ReadingStationEducationalReportCollection($data, $perPage, $page))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
