<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Http\Requests\ReadingStationAbsentPresentReportRequest;
use App\Http\Requests\ReadingStationEducationalReportRequest;
use App\Http\Requests\ReadingStationManagerReportRequest;
use App\Http\Requests\ReadingStationReadingStaticsReportRequest;
use App\Http\Requests\ReadingStationStudentReportRequest;
use App\Http\Resources\ReadingStationAbsentPresentReportCollection;
use App\Http\Resources\ReadingStationAbsentReportCollection;
use App\Http\Resources\ReadingStationEducationalReportCollection;
use App\Http\Resources\ReadingStationEducationalReportResource;
use App\Http\Resources\ReadingStationExitReportCollection;
use App\Http\Resources\ReadingStationLateReportCollection;
use App\Http\Resources\ReadingStationReadingStaticsReportCollection;
use App\Http\Resources\ReadingStationStrikeReportCollection;
use App\Http\Resources\ReadingStationStudentReportCollection;
use App\Http\Resources\ReadingStudentsExcelResource;
use App\Jobs\ExportFinished;
use App\Models\ReadingStation;
use App\Models\ReadingStationAbsentPresent;
use App\Models\ReadingStationSlutUser;
use App\Models\ReadingStationUser;
use App\Models\ReadingStationUserStrike;
use App\Models\ReadingStationWeeklyProgram;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Jobs\QueueExport;

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
        if ($request->exists('reading_station_slut_id')) {
            $slutUsers->whereHas('slut', function ($q) use ($request) {
                $q->where('id', $request->reading_station_slut_id);
            });
        }
        $slutUsers->whereDate('day', '>=', $request->from_date);
        $slutUsers->whereDate('day', '<=', $request->to_date);
        $slutUsers->withAggregate('weeklyProgram', 'reading_station_user_id');

        $perPage = $request->per_page ?? env('PAGE_COUNT');
        $page = $request->page ?? 1;
        $data = $slutUsers->get();
        return (new ReadingStationEducationalReportCollection($data, $perPage, $page, $request->sort, $request->sort_dir))->additional([
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
                $q2->where('reading_station_id', $readingStation->id)
                    ->where('table_number', '!=', null);
            });
        })->where('status', '!=', 'defined');
        if ($request->exists('reading_station_slut_id')) {
            $slutUsers->whereHas('slut', function ($q) use ($request) {
                $q->where('id', $request->reading_station_slut_id);
            });
        }
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
        $data = $slutUsers->with('absenseReason')->paginate($perPage);

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

    public function exit(ReadingStationAbsentPresentReportRequest $request, ReadingStation $readingStation)
    {
        $from = Carbon::parse($request->from_date);
        $to = Carbon::parse($request->to_date);
        if ($from->greaterThan($to)) {
            return (new ReadingStationEducationalReportResource(null))->additional([
                'errors' => ['reading_station_report' => ['from date should be before to date!']],
            ])->response()->setStatusCode(400);
        }
        $absentPresents = ReadingStationAbsentPresent::where('reading_station_id', $readingStation->id);
        if ($request->exists('name') || $request->exists('table_number')) {
            $absentPresents->whereHas('user', function ($q1) use ($request) {
                if ($request->exists('name')) {
                    $q1->where(DB::raw("CONCAT(IFNULL(first_name, ''), IFNULL(CONCAT(' ', last_name), ''))"), 'like', '%' . $request->name . '%');
                }
                if ($request->exists('table_number')) {
                    $q1->whereHas('readingStationUser', function ($q2) use ($request) {
                        $q2->where('table_number', $request->table_number);
                    });
                }
            });
        }
        if ($request->exists('reading_station_slut_id')) {
            $absentPresents->where('reading_station_slut_user_exit_id', $request->reading_station_slut_user_exit_id);
        }
        $absentPresents->whereDate('day', '>=', $request->from_date);
        $absentPresents->whereDate('day', '<=', $request->to_date);
        $perPage = $request->per_page ?? env('PAGE_COUNT');
        $sort = $request->sort ?? 'day';
        $sortDir = $request->sort_dir ?? 'asc';
        $absentPresents->orderBy($sort, $sortDir);
        $count = $absentPresents->count();
        $data = $absentPresents->paginate($perPage);

        return (new ReadingStationExitReportCollection($data, $count))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function late(ReadingStationAbsentPresentReportRequest $request, ReadingStation $readingStation)
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
            $slutUsers->whereHas('slut', function ($q) use ($request) {
                $q->where('id', $request->reading_station_slut_id);
            });
        }
        $slutUsers->where('status', 'like', 'late_%');
        $slutUsers->whereDate('day', '>=', $request->from_date);
        $slutUsers->whereDate('day', '<=', $request->to_date);
        $perPage = $request->per_page ?? env('PAGE_COUNT');
        $sort = $request->sort ?? 'day';
        $sortDir = $request->sort_dir ?? 'asc';
        $slutUsers->orderBy($sort, $sortDir);
        $count = $slutUsers->count();
        $data = $slutUsers->paginate($perPage);

        return (new ReadingStationLateReportCollection($data, $count))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function absent(ReadingStationAbsentPresentReportRequest $request, ReadingStation $readingStation)
    {
        $from = Carbon::parse($request->from_date);
        $to = Carbon::parse($request->to_date);
        if ($from->greaterThan($to)) {
            return (new ReadingStationEducationalReportResource(null))->additional([
                'errors' => ['reading_station_report' => ['from date should be before to date!']],
            ])->response()->setStatusCode(400);
        }
        $diff = intval(env('READING_ABSENT_REPORT_DATE_DIFF') ?? 7);
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
        if ($request->exists('reading_station_slut_id')) {
            $slutUsers->whereHas('slut', function ($q) use ($request) {
                $q->where('id', $request->reading_station_slut_id);
            });
        }
        $slutUsers->where('status', 'absent');
        $slutUsers->whereDate('day', '>=', $request->from_date);
        $slutUsers->whereDate('day', '<=', $request->to_date);
        $perPage = $request->per_page ?? env('PAGE_COUNT');
        $sort = $request->sort ?? 'day';
        $sortDir = $request->sort_dir ?? 'asc';
        $slutUsers->orderBy($sort, $sortDir);
        $all =  $slutUsers->get();
        $count = $all->count();
        $approvedCount = $all->where('absense_approved_status', 'approved')->count();
        $semiApprovedCount = $all->where('absense_approved_status', 'semi_approved')->count();
        $notApprovedCount = $count - $approvedCount - $semiApprovedCount;
        $data = $slutUsers->paginate($perPage);
        return (new ReadingStationAbsentReportCollection($data, $count, $approvedCount, $semiApprovedCount, $notApprovedCount))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function student(ReadingStationStudentReportRequest $request, ReadingStation $readingStation)
    {
        $students = User::whereHas('readingStationUser', function ($q1) use ($readingStation) {
            $q1->where('reading_station_id', $readingStation->id);
        });
        $sort = $request->sort ?? 'updated_at';
        $sortDir = $request->sort_dir ?? 'desc';
        $students->orderBy($sort, $sortDir);
        $perPage = $request->per_page ?? env('PAGE_COUNT');
        $data = $students->paginate($perPage);

        return (new ReadingStationStudentReportCollection($data))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function manager(ReadingStationManagerReportRequest $request, ReadingStation $readingStation)
    {
        $from = Carbon::parse($request->from_date);
        $to = Carbon::parse($request->to_date);
        if ($from->greaterThan($to)) {
            return (new ReadingStationEducationalReportResource(null))->additional([
                'errors' => ['reading_station_report' => ['from date should be before to date!']],
            ])->response()->setStatusCode(400);
        }
        $diff = intval(env('READING_MANAGER_REPORT_DATE_DIFF') ?? 7);
        if ($to->diffInDays($from) > $diff) {
            return (new ReadingStationEducationalReportResource(null))->additional([
                'errors' => ['reading_station_report' => ['report time difference is higher than it should be!', $diff]],
            ])->response()->setStatusCode(400);
        }

        $studentCount = User::whereHas('readingStationUser', function ($q) use ($readingStation) {
            $q->where('reading_station_id', $readingStation->id);
        })->count();
        $readingTotal = 0;
        $noneAbsents = ReadingStationSlutUser::whereHas('slut', function ($q) use ($readingStation) {
            $q->where('reading_station_id', $readingStation->id);
        })
            ->where('day', '>=', $from)
            ->where('day', '<=', $to)
            ->where(function ($q) {
                $q->where('status', 'present')->orWhere('status', 'like', 'late_%');
            })
            ->get();
        $noneAbsents->map(function ($userSlut) use (&$readingTotal) {
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
            }
            $readingTotal += $time;
        });
        $avarageReading = $readingTotal / $studentCount;
        $totals = ReadingStationUser::where('reading_station_id', $readingStation->id)
            ->sum('total');
        $avagrageUserTotal = $totals / $studentCount;
        $absents = ReadingStationSlutUser::whereHas('slut', function ($q) use ($readingStation) {
            $q->where('reading_station_id', $readingStation->id);
        })
            ->where('status', 'absent')
            ->get();
        $approvedAbsentsCount = $absents->where('absense_approved_status', 'approved')->count();
        $semiApprovedAbsentsCount = $absents->where('absense_approved_status', 'semi_approved')->count();
        $notApprovedAbsentsCount = count($absents) - $approvedAbsentsCount - $semiApprovedAbsentsCount;
        $lateCount = $noneAbsents->filter(function ($userSlut) {
            return str_starts_with($userSlut->status, 'late_');
        })->count();

        return [
            'studentCount' => $studentCount,
            'avarageReading' => $avarageReading,
            'avagrageUserTotal' => $avagrageUserTotal,
            'approvedAbsentsCount' => $approvedAbsentsCount,
            'semiApprovedAbsentsCount' => $semiApprovedAbsentsCount,
            'notApprovedAbsentsCount' => $notApprovedAbsentsCount,
            'lateCount' => $lateCount,
        ];
    }

    public function export(ReadingStation $readingStation)
    {
        $storage = env('DEFAULT_STORAGE', 'ftp');
        $disk = Storage::disk($storage);
        $fileName = $readingStation->id . '_' . 'users';
        if ($storage === 'ftp') {
            $fileName = env('FTP_PATH') . '/' . $fileName;
        }
        if ($disk->exists($fileName . '.xlsx')) {
            $time = Carbon::parse($disk->lastModified($fileName . '.xlsx'))->diffInSeconds(Carbon::now());
            if ($time > 120) {
                $disk->delete($fileName . '.xlsx');
            }
        }
        if (!$disk->exists($fileName . '.xlsx')) {
            if (!$disk->exists($fileName . '.tmp')) {
                $disk->put($fileName . '.tmp', '');

                Excel::store(new UsersExport($readingStation->id), $fileName . '.xlsx', $storage)->chain([
                    new ExportFinished($fileName . '.tmp'),
                ]);
            }
            return (new ReadingStudentsExcelResource('in progress'))->additional([
                'errors' => null,
            ])->response()->setStatusCode(201);
        } else {
            return Storage::drive($storage)->download($fileName . '.xlsx');
        }
    }
}
