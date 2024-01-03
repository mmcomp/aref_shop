<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationAbsentPresentReportRequest;
use App\Http\Requests\ReadingStationEducationalReportRequest;
use App\Http\Requests\ReadingStationReadingStaticsReportRequest;
use App\Http\Resources\ReadingStationAbsentPresentReportCollection;
use App\Http\Resources\ReadingStationEducationalReportCollection;
use App\Http\Resources\ReadingStationEducationalReportResource;
use App\Http\Resources\ReadingStationReadingStaticsReportCollection;
use App\Http\Resources\ReadingStationStrikeReportCollection;
use App\Models\ReadingStation;
use App\Models\ReadingStationSlutUser;
use App\Models\ReadingStationUserStrike;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReadingStationReportController extends Controller
{
    public function educational(ReadingStationEducationalReportRequest $request, ReadingStation $readingStation)
    {
        $from = Carbon::parse($request->from_date);
        $to = Carbon::parse($request->to_date);
        if ($from->greaterThan($to)) {
            return (new ReadingStationEducationalReportResource(null))->additional([
                'errors' => ['reading_station_report' => ['from date should be before to date!']],
            ])->response()->setStatusCode(400);
        }
        $diff = intval(env('EDUCATIONAL_REPORT_DATE_DIFF') ?? 7);
        if ($to->diffInDays($from) > $diff) {
            return (new ReadingStationEducationalReportResource(null))->additional([
                'errors' => ['reading_station_report' => ['report time difference is higher than it should be!', $diff]],
            ])->response()->setStatusCode(400);
        }
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

    public function readingStatics(ReadingStationReadingStaticsReportRequest $request, ReadingStation $readingStation)
    {
        $from = Carbon::parse($request->from_date);
        $to = Carbon::parse($request->to_date);
        if ($from->greaterThan($to)) {
            return (new ReadingStationEducationalReportResource(null))->additional([
                'errors' => ['reading_station_report' => ['from date should be before to date!']],
            ])->response()->setStatusCode(400);
        }
        $diff = intval(env('READING_STATICS_REPORT_DATE_DIFF') ?? 7);
        if ($to->diffInDays($from) > $diff) {
            return (new ReadingStationEducationalReportResource(null))->additional([
                'errors' => ['reading_station_report' => ['report time difference is higher than it should be!', $diff]],
            ])->response()->setStatusCode(400);
        }
        $slutUsers = ReadingStationSlutUser::whereHas('weeklyProgram', function ($q1) use ($readingStation) {
            $q1->whereHas('readingStationUser', function ($q2) use ($readingStation) {
                $q2->where('reading_station_id', $readingStation->id);
            });
        });
        $slutUsers->whereDate('day', '>=', $request->from_date);
        $slutUsers->whereDate('day', '<=', $request->to_date);
        $slutUsers->withAggregate('weeklyProgram', 'reading_station_user_id');
        $data = $slutUsers->get();

        return (new ReadingStationReadingStaticsReportCollection($data))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function absentPresent(ReadingStationAbsentPresentReportRequest $request, ReadingStation $readingStation)
    {
        $from = Carbon::parse($request->from_date);
        $to = Carbon::parse($request->to_date);
        if ($from->greaterThan($to)) {
            return (new ReadingStationEducationalReportResource(null))->additional([
                'errors' => ['reading_station_report' => ['from date should be before to date!']],
            ])->response()->setStatusCode(400);
        }
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
        if ($request->exists('reading_station_slut_id')) {
            $slutUsers->where('reading_station_slut_id', $request->reading_station_slut_id);
        }
        $slutUsers->whereDate('day', '>=', $request->from_date);
        $slutUsers->whereDate('day', '<=', $request->to_date);
        $perPage = $request->per_page ?? env('PAGE_COUNT');
        $sort = $request->sort ?? 'day';
        $sortDir = $request->sort_dir ?? 'asc';
        $slutUsers->orderBy($sort, $sortDir);
        $count = $slutUsers->count();
        $data = $slutUsers->paginate($perPage);

        return (new ReadingStationAbsentPresentReportCollection($data, $count))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function strike(ReadingStationAbsentPresentReportRequest $request, ReadingStation $readingStation)
    {
        $from = Carbon::parse($request->from_date);
        $to = Carbon::parse($request->to_date);
        if ($from->greaterThan($to)) {
            return (new ReadingStationEducationalReportResource(null))->additional([
                'errors' => ['reading_station_report' => ['from date should be before to date!']],
            ])->response()->setStatusCode(400);
        }
        $userStrikes = ReadingStationUserStrike::whereHas('readingStationSlutUser', function ($q0) use ($readingStation, $request) {
            if ($request->exists('reading_station_slut_id')) {
                $q0->where('reading_station_slut_id', $request->reading_station_slut_id);
            }
            $q0->whereHas('weeklyProgram', function ($q1) use ($readingStation, $request) {
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
        });
        $userStrikes->whereDate('day', '>=', $request->from_date);
        $userStrikes->whereDate('day', '<=', $request->to_date);
        $perPage = $request->per_page ?? env('PAGE_COUNT');
        $sort = $request->sort ?? 'day';
        $sortDir = $request->sort_dir ?? 'asc';
        $userStrikes->orderBy($sort, $sortDir);
        $count = $userStrikes->count();
        $data = $userStrikes->paginate($perPage);

        return (new ReadingStationStrikeReportCollection($data, $count))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
