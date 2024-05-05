<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationSlutUsersCreateRequest;
use App\Http\Requests\ReadingStationSlutUsersNextWeekPackageChangeRequest;
use App\Http\Requests\ReadingStationUserAbsentIndexRequest;
use App\Http\Requests\ReadingStationUserAbsentsIndexRequest;
use App\Http\Resources\ReadingStationSlutUserAbsents2Collection;
use App\Http\Resources\ReadingStationSlutUserAbsentsCollection;
use App\Http\Resources\ReadingStationSlutUserAvailableWeeklyProgramCollection;
use App\Http\Resources\ReadingStationSlutUserBeingWeeklyProgramCollection;
use App\Http\Resources\ReadingStationSlutUserLatesCollection;
use App\Http\Resources\ReadingStationSlutUserPackageWeeklyProgramCollection;
use App\Http\Resources\ReadingStationSlutUsersResource;
use App\Http\Resources\ReadingStationSlutUserWeeklyProgramCollection;
use App\Http\Resources\ReadingStationUserWeeklyProgramStructureResource;
use App\Http\Resources\ReadingStationWeeklyProgramHoursResource;
use App\Http\Resources\ReadingStationWeeklyPrograms3Collection;
use App\Http\Resources\ReadingStationWeeklyPrograms4Resource;
use App\Models\ReadingStation;
use App\Models\ReadingStationPackage;
use App\Models\ReadingStationSlut;
use App\Models\ReadingStationSlutUser;
use App\Models\ReadingStationUser;
use App\Models\ReadingStationWeeklyProgram;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        if ($week === 'current') {
            $start = Carbon::now()->startOfWeek(Carbon::SATURDAY)->toDateString();
            $end = Carbon::now()->endOfWeek(Carbon::FRIDAY)->toDateString();
        } else {
            $start = $nextWeekStart;
            $end = $nextWeekEnd;
        }
        $readingStationUser = $user->readingStationUser;
        if ($readingStationUser->contract_end && Carbon::parse($end)->greaterThan(Carbon::parse($readingStationUser->contract_end))) {
            throw new HttpException(400, 'User contract end is before the requested week end!');
        }
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
            $weeklyProgram->start = $start;
            $weeklyProgram->end = $end;
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
        $curentWeeklyProgram = $user->readingStationUser->weeklyPrograms->where('start', Carbon::now()->startOfWeek(Carbon::SATURDAY)->toDateString())->first();
        $readingStationUser = $user->readingStationUser;
        if ($readingStationUser->contract_end && Carbon::parse($nextWeekEnd)->greaterThan(Carbon::parse($readingStationUser->contract_end))) {
            return (new ReadingStationSlutUsersResource(null))->additional([
                'errors' => ['reading_station_slut_user' => ['User contract end is before the requested week end!']],
            ])->response()->setStatusCode(400);
        }
        if (!$curentWeeklyProgram) {
            if ($readingStationUser->contract_end && Carbon::parse(Carbon::now()->endOfWeek(Carbon::FRIDAY)->toDateString())->greaterThan(Carbon::parse($readingStationUser->contract_end))) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_slut_user' => ['User contract end is before the requested week end!']],
                ])->response()->setStatusCode(400);
            }
            $weeklyProgram = new ReadingStationWeeklyProgram();
            $weeklyProgram->reading_station_user_id = $user->readingStationUser->id;
            $weeklyProgram->name = $readingStationUser->package->name;
            $weeklyProgram->required_time = $readingStationUser->package->required_time;
            $weeklyProgram->optional_time = $readingStationUser->package->optional_time;
            $weeklyProgram->start = Carbon::now()->startOfWeek(Carbon::SATURDAY)->toDateString();
            $weeklyProgram->end = Carbon::now()->endOfWeek(Carbon::FRIDAY)->toDateString();
            $weeklyProgram->save();
        }
        foreach ($weeklyPrograms as $weeklyProgram) {
            if (Carbon::parse($weeklyProgram->start)->diffInDays($nextWeekStartDate) === 0) {
                if ($weeklyProgram->name !== $package->name && count($weeklyProgram->sluts) === 0) {
                    $weeklyProgram->name = $package->name;
                    $weeklyProgram->required_time = $package->required_time;
                    $weeklyProgram->optional_time = $package->optional_time;
                    $weeklyProgram->save();
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
        $readingStationUser = ReadingStationUser::where('user_id', $user->id)->withTrashed()->first();
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if ($readingStationUser->readingStation->id !== Auth::user()->reading_station_id) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        $weeklyPrograms = ReadingStationWeeklyProgram::where('reading_station_user_id', $readingStationUser->id)->orderBy('start', 'desc')->get();
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
        $perPage = $request->per_page ?? env('PAGE_COUNT');
        $output = $slutUsers->get();

        return (new ReadingStationSlutUserAbsents2Collection($output, $perPage, $request->page ?? 1))->additional([
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
        $perPage = $request->per_page ?? env('PAGE_COUNT');
        $output = $slutUsers->get();


        return (new ReadingStationSlutUserLatesCollection($output, $perPage, $request->page ?? 1))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function availables(ReadingStationUserAbsentsIndexRequest $request, User $user)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if ($user->readingStationUser->readingStation->id !== Auth::user()->reading_station_id) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        $end = Carbon::now()->endOfWeek(Carbon::FRIDAY)->toDateString();
        $weeklyPrograms = ReadingStationWeeklyProgram::where('end', '!=', $end);
        $weeklyPrograms->whereHas('readingStationUser', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
        $all = $weeklyPrograms->get();
        $total = 0;
        $all->map(function ($weeklyProgram) use (&$total) {
            $point = 0;
            $toDo = $weeklyProgram->readingStationUser->package->required_time + $weeklyProgram->readingStationUser->package->optional_time;
            $done =  $weeklyProgram->required_time_done + $weeklyProgram->optional_time_done;
            // if ($done < $toDo) {
            //     $point = -2;
            // } else {
            //     $step = $weeklyProgram->readingStationUser->package->step ?? 10;
            //     $point = ($done - ($done % $step)) * 2 / $step;
            // }
            if ($done < $toDo) {
                $point = -2;
            } else {
                $step = ($weeklyProgram->readingStationUser->package->step ?? 10) * 60;
                $extra = $done - $toDo;
                if ($extra > 0) {
                    $point = intval($extra/ $step) * 2;
                }
            }
            $total += $point;
        });

        $sort = "end";
        $sortDir = "desc";

        $weeklyPrograms->orderBy($sort, $sortDir);
        $perPage = $request->per_page;
        if (!$perPage) {
            $perPage = env('PAGE_COUNT');
        }
        if ($request->per_page === 'all') {
            $output = $weeklyPrograms->get();
        } else {
            $output = $weeklyPrograms->paginate($perPage);
        }

        return (new ReadingStationSlutUserAvailableWeeklyProgramCollection($output, $total))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function being(ReadingStationUserAbsentsIndexRequest $request, User $user)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if ($user->readingStationUser->readingStation->id !== Auth::user()->reading_station_id) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        $end = Carbon::now()->endOfWeek(Carbon::FRIDAY)->toDateString();
        $weeklyPrograms = ReadingStationWeeklyProgram::where('end', '!=', $end);
        $weeklyPrograms->whereHas('readingStationUser', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
        $weeklyPrograms->where('being_point', '>', 0);
        $all = clone $weeklyPrograms;
        $comp = clone $weeklyPrograms;
        $unCompletedWeeklyPrograms = $comp->whereDoesntHave('sluts', function ($q) {
            $q->where('status', '!=', 'defined');
        })->pluck('id')->toArray();
    
        $total = $all->whereNotIn('id', $unCompletedWeeklyPrograms)->sum('being_point');

        $sort = "end";
        $sortDir = "desc";

        $weeklyPrograms->orderBy($sort, $sortDir);
        $perPage = $request->per_page;
        if (!$perPage) {
            $perPage = env('PAGE_COUNT');
        }
        if ($request->per_page === 'all') {
            $output = $weeklyPrograms->get();
        } else {
            $output = $weeklyPrograms->paginate($perPage);
        }

        return (new ReadingStationSlutUserBeingWeeklyProgramCollection($output, $total, $unCompletedWeeklyPrograms))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function package(ReadingStationUserAbsentsIndexRequest $request, User $user)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if ($user->readingStationUser->readingStation->id !== Auth::user()->reading_station_id) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        $end = Carbon::now()->endOfWeek(Carbon::FRIDAY)->toDateString();
        $weeklyPrograms = ReadingStationWeeklyProgram::where('end', '!=', $end);
        $weeklyPrograms->whereHas('readingStationUser', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
        $weeklyPrograms->where('package_point', '>', 0);
        $total = $weeklyPrograms->sum('package_point');

        $sort = "end";
        $sortDir = "desc";

        $weeklyPrograms->orderBy($sort, $sortDir);
        $perPage = $request->per_page;
        if (!$perPage) {
            $perPage = env('PAGE_COUNT');
        }
        if ($request->per_page === 'all') {
            $output = $weeklyPrograms->get();
        } else {
            $output = $weeklyPrograms->paginate($perPage);
        }

        return (new ReadingStationSlutUserPackageWeeklyProgramCollection($output, $total))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function loadUser(User $user)
    {
        if ($user->id !== Auth::user()->id) {
            throw new HttpException(401, 'You do not have access here!');
        }

        return $this->load($user);
    }

    public function storeUser(ReadingStationSlutUsersCreateRequest $request, User $user)
    {
        if ($user->id !== Auth::user()->id) {
            throw new HttpException(401, 'You do not have access here!');
        }

        return $this->store($request, $user);
    }

    public function weeklyProgramListUser(User $user)
    {
        if ($user->id !== Auth::user()->id) {
            throw new HttpException(401, 'You do not have access here!');
        }

        return $this->weeklyProgramList($user);
    }

    public function loadWeeklyProgramUser(User $user, ReadingStationWeeklyProgram $weeklyProgram)
    {
        if ($user->id !== Auth::user()->id) {
            throw new HttpException(401, 'You do not have access here!');
        }

        return $this->loadWeeklyProgram($user, $weeklyProgram);
    }

    public function loadSummaryWeeklyProgramUser(User $user, ReadingStationWeeklyProgram $weeklyProgram)
    {
        if ($user->id !== Auth::user()->id) {
            throw new HttpException(401, 'You do not have access here!');
        }

        return $this->loadSummaryWeeklyProgram($user, $weeklyProgram);
    }

    public function loadHoursWeeklyProgramUser(User $user, ReadingStationWeeklyProgram $weeklyProgram)
    {
        if ($user->id !== Auth::user()->id) {
            throw new HttpException(401, 'You do not have access here!');
        }

        return $this->loadHoursWeeklyProgram($user, $weeklyProgram);
    }
}
