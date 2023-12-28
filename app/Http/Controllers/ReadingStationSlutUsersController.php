<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationSlutUsersCreateRequest;
use App\Http\Requests\ReadingStationSlutUsersNextWeekPackageChangeRequest;
use App\Http\Requests\ReadingStationUserAbsentIndexRequest;
use App\Http\Requests\ReadingStationUserAbsentsIndexRequest;
use App\Http\Resources\ReadingStationSlutUserAbsents2Collection;
use App\Http\Resources\ReadingStationSlutUserAbsentsCollection;
use App\Http\Resources\ReadingStationSlutUsersResource;
use App\Http\Resources\ReadingStationUserWeeklyProgramStructureResource;
use App\Http\Resources\ReadingStationWeeklyProgramHoursResource;
use App\Http\Resources\ReadingStationWeeklyPrograms3Collection;
use App\Http\Resources\ReadingStationWeeklyPrograms4Resource;
use App\Models\ReadingStation;
use App\Models\ReadingStationPackage;
use App\Models\ReadingStationSlut;
use App\Models\ReadingStationSlutUser;
use App\Models\ReadingStationWeeklyProgram;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReadingStationSlutUsersController extends Controller
{
    private function getNextWeek()
    {
        $nextWeekStartDate = Carbon::now()->endOfWeek(Carbon::FRIDAY)->addDay();
        $nextWeekEndDate = $nextWeekStartDate->clone()->endOfWeek(Carbon::FRIDAY);
        return [$nextWeekStartDate, $nextWeekEndDate];
    }

    private function getWeeklyProgram(User $user, $week): ReadingStationWeeklyProgram
    {
        $package = $user->readingStationUser->package;
        $weeklyPrograms = $user->readingStationUser->weeklyPrograms;
        $weeklyProgram = null;
        [$nextWeekStartDate, $nextWeekEndDate] = $this->getNextWeek();
        $nextWeekStart = $nextWeekStartDate->toDateString();
        $nextWeekEnd = $nextWeekEndDate->toDateString();
        foreach ($weeklyPrograms as $weekProgram) {
            if ($week === 'current' && Carbon::now()->between(Carbon::parse($weekProgram->start), Carbon::parse($weekProgram->end), true)) {
                $weeklyProgram = $weekProgram;
                break;
            } else if ($week === 'next' && $nextWeekStart === $weekProgram->start && $nextWeekEnd === $weekProgram->end) {
                $weeklyProgram = $weekProgram;
                break;
            }
        }
        if (!$weeklyProgram) {
            $weeklyProgram = new ReadingStationWeeklyProgram();
            $weeklyProgram->reading_station_user_id = $user->readingStationUser->id;
            $weeklyProgram->name = $package->name;
            $weeklyProgram->required_time = $package->required_time;
            $weeklyProgram->optional_time = $package->optional_time;
            if ($week === 'current') {
                $weeklyProgram->start = Carbon::now()->startOfWeek(Carbon::SATURDAY)->toDateString();
                $weeklyProgram->end = Carbon::now()->endOfWeek(Carbon::FRIDAY)->toDateString();
            } else {
                $weeklyProgram->start = $nextWeekStart;
                $weeklyProgram->end = $nextWeekEnd;
            }
            $weeklyProgram->save();
        }

        return $weeklyProgram;
    }

    public function store(ReadingStationSlutUsersCreateRequest $request, User $user)
    {
        if (!$user->is_reading_station_user) {
            return (new ReadingStationSlutUsersResource(null))->additional([
                'errors' => ['reading_station_slut_user' => ['User does not have blong to any Reading Station!']],
            ])->response()->setStatusCode(400);
        }
        $sluts = $user->readingStationUser->readingStation->sluts;
        $weeklyProgram = $this->getWeeklyProgram($user, $request->week);
        if (ReadingStationSlutUser::where("reading_station_weekly_program_id", $weeklyProgram->id)->first()) {
            if (!in_array(Auth::user()->group->type, ['admin', 'admin_reading_station', 'admin_reading_station_branch', 'user_reading_station_branch'])) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_slut_user' => ['User has a program for the requested week!']],
                ])->response()->setStatusCode(400);
            }
            if (ReadingStationSlutUser::where("reading_station_weekly_program_id", $weeklyProgram->id)->where('status', '!=', 'defined')->first()) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_slut_user' => ['User has a recorded slut for the requested week!']],
                ])->response()->setStatusCode(400);
            }
            ReadingStationSlutUser::where("reading_station_weekly_program_id", $weeklyProgram->id)->delete();
        }
        $total = 0;
        $readingStation = $user->readingStationUser->readingStation;
        $offDays = $readingStation->offdays;
        foreach ($request->data as $data) {
            if (!Carbon::parse($data['day'])->between(Carbon::parse($weeklyProgram->start), Carbon::parse($weeklyProgram->end), true)) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_slut_user' => ['The selected day is not in the week!']],
                ])->response()->setStatusCode(400);
            }
            if ($offDays->where('offday', $data['day'])->first()) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_slut_user' => ['The selected day is a off day!', $data['day']]],
                ])->response()->setStatusCode(400);
            }
            $theSlut = ReadingStationSlut::find($data['reading_station_slut_id']);
            $total += $theSlut->duration;
        }
        $requiredTime = $weeklyProgram->required_time + 60;
        if ($total > $requiredTime) {
            return (new ReadingStationSlutUsersResource(null))->additional([
                'errors' => ['reading_station_slut_user' => ['Total time exceeded the maximum!']],
            ])->response()->setStatusCode(400);
        }


        $requestSluts = [];
        foreach ($request->data as $data) {
            $requestSluts[] = $data['reading_station_slut_id'];
        }
        $availableSluts = [];
        foreach ($sluts as $slut) {
            $availableSluts[] = $slut->id;
        }
        if (count(array_intersect($requestSluts, $availableSluts)) !== count($requestSluts)) {
            $intersect = array_intersect($requestSluts, $availableSluts);
            $diff = array_diff($requestSluts, $intersect);
            return (new ReadingStationSlutUsersResource(null))->additional([
                'errors' => ['reading_station_slut_user' => ['Some of sluts are not correct!', $diff]],
            ])->response()->setStatusCode(400);
        }
        $query = [];
        foreach ($request->data as $data) {
            $query[] = [
                "reading_station_weekly_program_id" => $weeklyProgram->id,
                "reading_station_slut_id" => $data['reading_station_slut_id'],
                "day" => $data['day'],
                "is_required" => true,
                "status" => "defined",
                "created_at" => Carbon::now()->toDateString(),
                "updated_at" => Carbon::now()->toDateString(),
            ];
        }
        ReadingStationSlutUser::insert($query);
        return (new ReadingStationSlutUsersResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    public function load(User $user)
    {
        return (new ReadingStationUserWeeklyProgramStructureResource($user))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function changeNextWeekPackage(ReadingStationSlutUsersNextWeekPackageChangeRequest $request, User $user)
    {
        [$nextWeekStartDate, $nextWeekEndDate] = $this->getNextWeek();
        $nextWeekStart = $nextWeekStartDate->toDateString();
        $nextWeekEnd = $nextWeekEndDate->toDateString();
        $package = ReadingStationPackage::find($request->next_week_package_id);
        $weeklyPrograms = $user->readingStationUser->weeklyPrograms;
        foreach ($weeklyPrograms as $indx => $weeklyProgram) {
            if (Carbon::parse($weeklyProgram->start)->diffInDays($nextWeekStartDate) === 0) {
                if ($weeklyProgram->name !== $package->name && count($weeklyProgram->sluts) === 0) {
                    $weekPr = ReadingStationWeeklyProgram::find($weeklyProgram->id);
                    $weekPr->name = $package->name;
                    $weekPr->required_time = $package->required_time;
                    $weekPr->optional_time = $package->optional_time;
                    $weekPr->save();
                }
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(204);
            }
        }
        $weeklyProgram = new ReadingStationWeeklyProgram();
        $weeklyProgram->reading_station_user_id = $user->readingStationUser->id;
        $weeklyProgram->name = $package->name;
        $weeklyProgram->required_time = $package->required_time;
        $weeklyProgram->optional_time = $package->optional_time;
        $weeklyProgram->start = $nextWeekStart;
        $weeklyProgram->end = $nextWeekEnd;
        $weeklyProgram->save();

        return (new ReadingStationSlutUsersResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    public function listAbsentUsers(ReadingStationUserAbsentIndexRequest $request, ReadingStation $readingStation)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if ($readingStation->id !== Auth::user()->reading_station_id) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }
        if (Carbon::parse($request->from_day)->greaterThan(Carbon::parse($request->to_day))) {
            return (new ReadingStationSlutUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['From Day is after To Day!']],
            ])->response()->setStatusCode(400);
        }


        $slutUsers = ReadingStationSlutUser::whereHas('slut', function ($q1) use ($readingStation, $request) {
            $q1->where('reading_station_id', $readingStation->id);
        })
            ->where('day', '>=', $request->from_day)
            ->where('day', '<=', $request->to_day)
            ->where('status', 'absent');
        if ($request->exists('user_id')) {
            $slutUsers->whereHas('weeklyProgram', function ($q1) use ($request) {
                $q1->whereHas('readingStationUser', function ($q2) use ($request) {
                    $q2->where('user_id', $request->user_id);
                });
            });
        }
        if ($request->exists('reading_station_slut_id')) {
            $slutUsers->where('reading_station_slut_id', $request->reading_station_slut_id);
        }

        $sort = "id";
        $sortDir = "desc";
        if ($request->exists('sort_dir')) {
            $sortDir = $request->sort_dir;
        }
        if ($request->exists('sort')) {
            $sort = $request->sort;
        }

        $slutUsers->orderBy($sort, $sortDir);
        $perPage = $request->per_page;
        if (!$perPage) {
            $perPage = env('PAGE_COUNT');
        }
        if ($request->per_page === 'all') {
            $output = $slutUsers->get();
        } else {
            $output = $slutUsers->paginate($perPage);
        }

        return (new ReadingStationSlutUserAbsentsCollection($output))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function weeklyProgramList(User $user)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if ($user->readingStationUser->readingStation->id !== Auth::user()->reading_station_id) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        $weeklyPrograms = ReadingStationWeeklyProgram::where('reading_station_user_id', $user->readingStationUser->id)->orderBy('start', 'desc')->get();
        return (new ReadingStationWeeklyPrograms3Collection($weeklyPrograms))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function loadWeeklyProgram(User $user, ReadingStationWeeklyProgram $weeklyProgram)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if ($user->readingStationUser->readingStation->id !== Auth::user()->reading_station_id) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        if ($weeklyProgram->readingStationUser->user->id !== $user->id) {
            return (new ReadingStationSlutUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['This weekly program does not belong to the User!']],
            ])->response()->setStatusCode(400);
        }
        return (new ReadingStationUserWeeklyProgramStructureResource($user, [$weeklyProgram]))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function loadSummaryWeeklyProgram(User $user, ReadingStationWeeklyProgram $weeklyProgram)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if ($user->readingStationUser->readingStation->id !== Auth::user()->reading_station_id) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        if ($weeklyProgram->readingStationUser->user->id !== $user->id) {
            return (new ReadingStationSlutUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['This weekly program does not belong to the User!']],
            ])->response()->setStatusCode(400);
        }
        $readingStation = $user->readingStationUser->readingStation;
        $start = $weeklyProgram->start;
        $end = $weeklyProgram->end;
        $users = $readingStation->users->pluck('id');
        $weeklyPrograms = ReadingStationWeeklyProgram::whereIn('reading_station_user_id', $users)
            ->where('start', $start)
            ->where('end', $end)
            ->get();
        $avarage_reading_hours = intval(($weeklyPrograms->sum('required_time_done') + $weeklyPrograms->sum('optional_time_done')) / count($users));
        $readingStationData = [
            'avarage_reading_minutes' => $avarage_reading_hours,
            'daily_avarage_reading_minutes' => intval($avarage_reading_hours / 7),
        ];
        return (new ReadingStationWeeklyPrograms4Resource($weeklyProgram, $readingStationData))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function loadHoursWeeklyProgram(User $user, ReadingStationWeeklyProgram $weeklyProgram)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if ($user->readingStationUser->readingStation->id !== Auth::user()->reading_station_id) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        if ($weeklyProgram->readingStationUser->user->id !== $user->id) {
            return (new ReadingStationSlutUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['This weekly program does not belong to the User!']],
            ])->response()->setStatusCode(400);
        }

        return (new ReadingStationWeeklyProgramHoursResource($weeklyProgram))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function absents(ReadingStationUserAbsentsIndexRequest $request, User $user)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if ($user->readingStationUser->readingStation->id !== Auth::user()->reading_station_id) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        $slutUsers = ReadingStationSlutUser::where('status', 'absent');
        $slutUsers->whereHas('weeklyProgram', function ($q1) use ($user) {
            $q1->whereHas('readingStationUser', function ($q2) use ($user) {
                $q2->where('user_id', $user->id);
            });
        });

        $sort = "day";
        $sortDir = "desc";

        $slutUsers->orderBy($sort, $sortDir);
        $perPage = $request->per_page;
        if (!$perPage) {
            $perPage = env('PAGE_COUNT');
        }
        if ($request->per_page === 'all') {
            $output = $slutUsers->get();
        } else {
            $output = $slutUsers->paginate($perPage);
        }

        return (new ReadingStationSlutUserAbsents2Collection($output))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function lates(ReadingStationUserAbsentsIndexRequest $request, User $user)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if ($user->readingStationUser->readingStation->id !== Auth::user()->reading_station_id) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        $slutUsers = ReadingStationSlutUser::where('status', 'like', 'late%');
        $slutUsers->whereHas('weeklyProgram', function ($q1) use ($user) {
            $q1->whereHas('readingStationUser', function ($q2) use ($user) {
                $q2->where('user_id', $user->id);
            });
        });

        $sort = "day";
        $sortDir = "desc";

        $slutUsers->orderBy($sort, $sortDir);
        $perPage = $request->per_page;
        if (!$perPage) {
            $perPage = env('PAGE_COUNT');
        }
        if ($request->per_page === 'all') {
            $output = $slutUsers->get();
        } else {
            $output = $slutUsers->paginate($perPage);
        }

        return (new ReadingStationSlutUserAbsents2Collection($output))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
