<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationCreateUserRequest;
use App\Http\Requests\ReadingStationSetUserAbsentPresentRequest;
use App\Http\Requests\ReadingStationSetUserSlutStatusRequest;
use App\Http\Requests\ReadingStationUpdateUserRequest;
use App\Http\Requests\ReadingStationUsersBulkUpdateRequest;
use App\Http\Requests\ReadingStationUsersIndexRequest;
use App\Http\Resources\ReadingStationAbsentPresentResource;
use App\Http\Resources\ReadingStationUsers2Collection;
use App\Http\Resources\ReadingStationUsersCollection;
use App\Http\Resources\ReadingStationUserSlutsResource;
use App\Http\Resources\ReadingStationUsersResource;
use App\Models\ReadingStation;
use App\Models\ReadingStationAbsentPresent;
use App\Models\ReadingStationPackage;
use App\Models\ReadingStationSlut;
use App\Models\ReadingStationSlutUser;
use App\Models\ReadingStationUser;
use App\Models\ReadingStationWeeklyProgram;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Utils\ReadingStationAuth;
use App\Http\Requests\ReadingStationChangeExitsRequest;
use App\Http\Requests\ReadingStationIndexExitsRequest;
use App\Http\Requests\ReadingStationIndexUserAbsentsRequest;
use App\Http\Requests\ReadingStationIndexUserAbsentTablesRequest;
use App\Http\Requests\ReadingStationIndexUserAbsentVerifyRequest;
use App\Http\Requests\ReadingStationNoneUsersIndexRequest;
use App\Http\Resources\NoneUserCollection;
use App\Http\Resources\ReadingStationExitsResource;
use App\Http\Resources\ReadingStationUserAbsentsCollection;
use App\Http\Resources\ReadingStationUserAbsentTablesCollection;
use App\Http\Resources\ReadingStationUsers5Collection;
use App\Http\Resources\UserCollection;
use App\Models\Group;
use App\Models\ReadingStationCall;
use App\Models\ReadingStationSlutChangeWarning;
use App\Utils\SlutUserValidation;
use Illuminate\Support\Facades\Storage;
use Log;

class ReadingStationUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ReadingStationUsersIndexRequest $request)
    {
        $isReadingStationBranchAdmin = in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch']);
        $sort = "id";
        $sortDir = "desc";
        $paginatedReadingStationUsers = [];
        $paginatedReadingStationUsers = ReadingStationUser::where('id', '>', 0);
        if ($isReadingStationBranchAdmin) {
            $readingStationId = Auth::user()->reading_station_id;
            if ($readingStationId === null) {
                return (new ReadingStationUsersCollection(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(200);
            }
            $paginatedReadingStationUsers->where('reading_station_id', $readingStationId);
        }
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sortDir = $request->get('sort_dir');
        }
        if ($request->get('per_page') == "all") {
            $paginatedReadingStationUsers = $paginatedReadingStationUsers->orderBy($sort, $sortDir)->get();
        } else {
            $perPage = $request->get('per_page');
            if (!$perPage) {
                $perPage = env('PAGE_COUNT');
            }
            $paginatedReadingStationUsers = $paginatedReadingStationUsers->orderBy($sort, $sortDir)->paginate($perPage);
        }
        return (new ReadingStationUsersCollection($paginatedReadingStationUsers))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function oneIndex(ReadingStationUsersIndexRequest $request, ReadingStation $readingStation)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if (Auth::user()->reading_station_id !== $readingStation->id) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }
        $sort = "id";
        $sortDir = "desc";
        $paginatedReadingStationOffdays = ReadingStationUser::/*select(['id', 'table_number', 'total', 'user_id', 'default_package_id'])->with(['user' => function ($query) {
            $query->select('id', 'first_name', 'last_name');
        }])*/with('user')
            ->with('package')
            ->with('weeklyPrograms.sluts')
            ->where("reading_station_id", $readingStation->id);
        $userGroup = Group::where('type', 'user')->first();
        if ($request->get('name') || $request->get('phone')) {
            $paginatedReadingStationOffdays
                ->whereHas('user', function ($q) use ($request, $userGroup) {
                    if ($request->get('name')) {
                        $name = $request->get('name');
                        $q->where(DB::raw("CONCAT(first_name, ' ',last_name)"), 'like', '%' . $name . '%');
                    }
                    if ($request->get('phone')) {
                        $phone = $request->get('phone');
                        $q->where('email', 'like', '%' . $phone . '%')
                            ->orWhere('home_tell', 'like', '%' . $phone . '%')
                            ->orWhere('father_cell', 'like', '%' . $phone . '%')
                            ->orWhere('mother_cell', 'like', '%' . $phone . '%');
                    }
                    $q->where('groups_id', $userGroup->id);
                });
        }
        if ($request->exists('status')) {
            $paginatedReadingStationOffdays->where('status', $request->get('status'));
        }
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sortDir = $request->get('sort_dir');
        }
        if ($request->get('per_page') == "all") {
            $paginatedReadingStationOffdays = $paginatedReadingStationOffdays->orderBy($sort, $sortDir)->get();
        } else {
            $perPage = $request->get('per_page');
            if (!$perPage) {
                $perPage = env('PAGE_COUNT');
            }
            $paginatedReadingStationOffdays = $paginatedReadingStationOffdays->orderBy($sort, $sortDir)->paginate($perPage);
        }
        return (new ReadingStationUsers2Collection($paginatedReadingStationOffdays))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    public function oneSlutIndex(ReadingStation $readingStation, ReadingStationSlut $slut)
    {
        $isReadingStationBranchAdmin = in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch']);
        if ($isReadingStationBranchAdmin) {
            $readingStationId = Auth::user()->reading_station_id;
            if ($readingStationId !== $readingStation->id) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }
        if ($slut->reading_station_id !== $readingStation->id) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station slut does not belong to the reading station!']],
            ])->response()->setStatusCode(400);
        }

        $forbiddenReadingStationUserIds = ReadingStationSlutUser::where('reading_station_id', $readingStation->id)->where('status', '!=', 'active')->pluck('id');
        $forbiddenWeeklyPrograms = ReadingStationWeeklyProgram::whereIn('reading_station_user_id', $forbiddenReadingStationUserIds)->pluck('id');

        $beforeSluts = ReadingStationSlut::where('reading_station_id', $readingStation->id)
            ->where('id', '!=', $slut->id)
            ->where('start', '<', $slut->start)
            ->pluck('id');
        // $beforeSluts = $readingStation->sluts->where('id', '!=', $slut->id)->where('start', '<', $slut->start)->pluck('id');
        if (
            count($beforeSluts) > 0 &&
            ReadingStationSlutUser::whereIn('reading_station_slut_id', $beforeSluts)
            ->whereNotIn('reading_station_weekly_program_id', $forbiddenWeeklyPrograms)
            ->where('is_required', 1)
            ->where('status', 'defined')
            ->where('day', Carbon::now()->toDateString())
            ->count() > 0
        ) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['There are defined statuses before this Slut!']],
            ])->response()->setStatusCode(400);
        }

        $end = Carbon::now()->endOfWeek(Carbon::FRIDAY)->toDateString();
        $start = Carbon::now()->startOfWeek(Carbon::SATURDAY)->toDateString();
        $now = Carbon::now()->toDateString();
        $slutUsers = ReadingStationUser::where('reading_station_id', $readingStation->id)
            ->where('status', 'active')
            // ->whereHas('weeklyPrograms', function ($query) use ($end) {
            //     $query->whereDate('end', $end);
            // })
            // ->whereHas('user', function ($query) use ($start, $end) {
            //     $query->whereHas('absentPresents', function ($q) use ($start, $end) {
            //         $q->whereDate('day', '>=', $start)
            //             ->whereDate('day', '<=', $end);
            //     });
            // })s
            ->with(['weeklyPrograms' => function ($q) use ($slut, $now) {
                $q->with(['sluts' => function ($q1) use ($slut, $now) {
                    $q1/*->where('reading_station_slut_id', $slut->id)*/
                        ->whereDate('day', $now);
                }]);
            }])
            ->with('user.absentPresents')
            ->orderBy('table_number')
            ->get();
        // $slutUsers = $readingStation->users->sortBy('table_number');
        // return  $slutUsers;
        return (new ReadingStationUserSlutsResource($slutUsers, $slut))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function getStatusTime(string $status, ReadingStationSlut $slut): int
    {
        $time = $slut->duration;
        switch ($status) {
            case 'late_15':
                $time -= 15;
                break;
            case 'late_30':
                $time -= 30;
                break;
            case 'late_45':
                $time -= 45;
                break;
            case 'late_60':
                $time -= 60;
                break;
            case 'present':
                break;
            default:
                $time = 0;
                break;
        }

        return $time;
    }

    public function setUserSlutStatus(ReadingStationSetUserSlutStatusRequest $request, ReadingStation $readingStation, User $user, ReadingStationSlut $slut)
    {
        if (!$this->checkUserWithReadingStationAuth($readingStation, $user)) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
            ])->response()->setStatusCode(400);
        }

        $userWeeklyPrograms = $user->readingStationUser->weeklyPrograms;
        $thisWeeklyProgram = $userWeeklyPrograms->where('end', Carbon::now()->endOfWeek(Carbon::FRIDAY)->toDateString())->first();
        if (!$thisWeeklyProgram || ($thisWeeklyProgram && count($thisWeeklyProgram->sluts) === 0)) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station user does not have a plan for this week!']],
            ])->response()->setStatusCode(400);
        }
        if (!$slut->is_sleep && $request->status === 'sleep') {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['The selected slut is not sleepable!']],
            ])->response()->setStatusCode(400);
        }

        $slutUserValidation = new SlutUserValidation($thisWeeklyProgram, $slut, $user, $request->status);
        $warnings = $slutUserValidation->start();

        $today = Carbon::now()->toDateString();
        $userSlut = $thisWeeklyProgram->sluts->where('reading_station_slut_id', $slut->id)->where('day', $today)->first();
        if ($request->status === 'defined' && $userSlut) {
            if ($userSlut->is_required) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['You can not change to defined for required sluts!']],
                ])->response()->setStatusCode(400);
            }
            // $nextUserSluts = ReadingStationSlutUser::withAggregate('slut', 'start')
            //     ->where('reading_station_weekly_program_id', $thisWeeklyProgram->id)
            //     ->where('day', $today)
            //     ->get()
            //     ->where('slut_start', '>', $slut->start)
            //     ->sortByDesc('slut_start')
            //     ->pluck('id');
            // ReadingStationSlutUser::whereIn('id', $nextUserSluts)->update(['status' => 'defined', 'user_id' => Auth::user()->id]);
        }


        $oldStatus = null;
        $deleted = false;

        if (!$userSlut) {
            if ($request->status === 'absent' || $request->status === 'defined') {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station user is not required for this slut!']],
                ])->response()->setStatusCode(400);
            }
            $userSlut = new ReadingStationSlutUser();
            $userSlut->reading_station_weekly_program_id = $thisWeeklyProgram->id;
            $userSlut->reading_station_slut_id = $slut->id;
            $userSlut->day = $today;
            $userSlut->is_required = false;
        } else {
            $oldStatus = $userSlut->status;
            if ($oldStatus === $request->status) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Duplicated request']],
                ])->response()->setStatusCode(400);
            }
            if ($request->status === 'defined' && !$userSlut->is_required) {
                $deleted = true;
            }
        }
        $userSlut->user_id = Auth::user()->id;
        $userSlut->status = $request->status;
        $userSlut->save();

        if (!$userSlut->absentPresent) {
            $absentPresent = new ReadingStationAbsentPresent();
            $absentPresent->user_id = $user->id;
            $absentPresent->reading_station_id = $readingStation->id;
            $absentPresent->day = $userSlut->day;
            $absentPresent->operator_id = Auth::user()->id;
            $absentPresent->save();
        }

        $weeklyProgram = $thisWeeklyProgram;
        $readingStationUser = $weeklyProgram->readingStationUser;
        $time = $userSlut->slut->duration;
        if ($oldStatus) {
            $oldTime = $this->getStatusTime($oldStatus, $slut);
            if ($userSlut->is_required) {
                $weeklyProgram->required_time_done -= $oldTime;
                if ($oldStatus === 'absent') {
                    $weeklyProgram->absent_day -= 1;
                    $weeklyProgram->absence_done -= $time;
                    $weeklyProgram->strikes_done -= 2;
                    $readingStationUser->total += 2;
                } else if ($oldStatus === 'present') {
                    $weeklyProgram->present_day -= 1;
                } else if (strpos($oldStatus, 'late_') === 0) {
                    $weeklyProgram->late_day -= 1;
                    $readingStationUser->total += 1;
                    $weeklyProgram->strikes_done -= 1;
                    if ($oldStatus === 'late_60_plus') {
                        $weeklyProgram->strikes_done -= 1;
                        $readingStationUser->total += 1;
                    }
                }
            } else {
                $weeklyProgram->optional_time_done -= $oldTime;
            }
        }
        $newTime = $this->getStatusTime($userSlut->status, $slut);
        if ($userSlut->is_required) {
            $weeklyProgram->required_time_done += $newTime;
            if ($userSlut->status === 'absent') {
                $weeklyProgram->absent_day += 1;
                $weeklyProgram->absence_done += $time;
                $weeklyProgram->strikes_done += 2;
                $readingStationUser->total -= 2;
            } else if ($userSlut->status === 'present') {
                $weeklyProgram->present_day += 1;
            } else if (strpos($userSlut->status, 'late_') === 0) {
                $weeklyProgram->late_day += 1;
                $readingStationUser->total -= 1;
                $weeklyProgram->strikes_done += 1;
                if ($userSlut->status === 'late_60_plus') {
                    $weeklyProgram->strikes_done += 1;
                    $readingStationUser->total -= 1;
                }
            }
        } else {
            $weeklyProgram->optional_time_done += $newTime;
        }
        /*
        if ($userSlut->status !== 'absent' && $userSlut->status !== 'defined') {
            switch ($userSlut->status) {
                case 'late_15':
                    $time -= 15;
                    break;
                case 'late_30':
                    $time -= 30;
                    break;
                case 'late_45':
                    $time -= 45;
                    break;
                case 'late_60':
                    $time -= 60;
                    break;
                case 'late_60_plus':
                    $time = 0;
                    break;
                case 'present':
                    $weeklyProgram->present_day += 1;
                    break;
            }

            if ($userSlut->is_required) {
                $weeklyProgram->required_time_done += $time;
                if ($oldStatus && $oldStatus === 'absent') {
                    $weeklyProgram->absent_day -= 1;
                }
            } else {
                $weeklyProgram->optional_time_done += $time;
            }
            if (str_starts_with('late_', $userSlut->status)) {
                $weeklyProgram->strikes_done += 1;
                $readingStationUser->total -= 1;
                if ($userSlut->status === 'late_60_plus') {
                    $weeklyProgram->strikes_done += 1;
                    $readingStationUser->total -= 1;
                }
                $weeklyProgram->late_day++;
            }
            $oldTime = 0;
            if ($oldStatus) {
                $oldTime = $this->getStatusTime($oldStatus, $userSlut->slut);
            }
            if ($userSlut->is_required) {
                $weeklyProgram->required_time_done -= $oldTime;
            } else {
                $weeklyProgram->optional_time_done -= $time;
            }
        } else if ($userSlut->is_required && $userSlut->status === 'absent') {
            $weeklyProgram->absence_done += $time;
            $weeklyProgram->strikes_done += 2;
            $weeklyProgram->absent_day += 1;
            $readingStationUser->total -= 2;
            if ($oldStatus && $oldStatus === 'present') {
                $weeklyProgram->present_day -= 1;
            }
        }
        */

        Log::info("wp" . json_encode($weeklyProgram));
        $weeklyProgram->save();
        $readingStationUser->save();
        if ($weeklyProgram->sluts->where('day', $today)->where('status', '!=', 'defined')->count() === 0) {
            $absentPresent = $userSlut->absentPresent;
            if ($absentPresent)
                $absentPresent->delete();
        }
        if ($deleted) {
            $userSlut->delete();
        } else if (count($warnings) > 0) {
            $query = [];
            foreach ($warnings as $warning) {
                $query[] = [
                    "reading_station_slut_user_id" => $userSlut->id,
                    "description" => $warning,
                    "is_read" => false,
                    "operator_id" => Auth::user()->id,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                ];
            }
            ReadingStationSlutChangeWarning::insert($query);
        }

        return (new ReadingStationUserSlutsResource([$userSlut->weeklyProgram->readingStationUser], $slut, $warnings))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ReadingStationCreateUserRequest $request, User $user)
    {
        $readingStation = ReadingStation::find($request->reading_station_id);

        if (!$this->checkUserWithReadingStationAuth($readingStation, $user)) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
            ])->response()->setStatusCode(400);
        }

        if ($readingStation->table_start_number > $request->table_number || $readingStation->table_end_number < $request->table_number) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station table does not exist!']],
            ])->response()->setStatusCode(400);
        }
        $found = ReadingStationUser::where("reading_station_id", $request->reading_station_id)->where("user_id", $user->id)->first();
        if ($found) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station user exists!']],
            ])->response()->setStatusCode(400);
        }

        if ($request->table_number !== null) {
            $found = ReadingStationUser::where("reading_station_id", $request->reading_station_id)->where("table_number", $request->table_number)->first();
            if ($found) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station user table is occupied!']],
                ])->response()->setStatusCode(400);
            }
        }
        $date = Carbon::parse($request->start_date);
        if (Carbon::now()->diffInDays($date) !== 0 && $date->isPast()) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station user start date should be in the future!']],
            ])->response()->setStatusCode(400);
        }
        $package = ReadingStationPackage::find($request->default_package_id);
        $requiredTime = $request->required_time;
        if (!$request->exists('required_time')) {
            $requiredTime = $package->required_time;
        }
        $optionalTime = $request->optional_time;
        if (!$request->exists('optional_time')) {
            $optionalTime = $package->optional_time;
        }
        $start = $date->toDateString();
        $end = $date->endOfWeek(Carbon::FRIDAY)->toDateString();


        $found = ReadingStationUser::where("reading_station_id", $request->reading_station_id)->where("user_id", $user->id)->withTrash()->first();
        if ($found) {
            $found->deleted_at = null;
            $found->status = 'active';
            $found->save();
            $id = $found->id;
        } else {
            $id = ReadingStationUser::create([
                "reading_station_id" => $request->reading_station_id,
                "user_id" => $user->id,
                "table_number" => $request->table_number,
                "default_package_id" => $request->default_package_id,
                "start_date" => $request->start_date,
                "required_time" => $requiredTime,
                "optional_time" => $optionalTime,
                "consultant" => $request->consultant,
                "representative" => $request->representative,
                "contract_start" => $request->contract_start,
                "contract_end" => $request->contract_end,
                "status" => 'active',
            ])->id;
        }
        ReadingStationUser::where('reading_station_id', '!=', $request->reading_station_id)->where("user_id", $user->id)->where('status', 'active')->withTrash()->update(['status' => 'relocated']);
        ReadingStationWeeklyProgram::create([
            "reading_station_user_id" => $id,
            "name" => $package->name,
            "start" => $start,
            "end" => $end,
            "required_time" => $requiredTime,
            "optional_time" => $optionalTime,
            "consultant" => $request->consultant,
            "representative" => $request->representative,
            "contract_start" => $request->contract_start,
            "contract_end" => $request->contract_end,
        ]);
        $user->is_reading_station_user = true;
        $user->save();

        return (new ReadingStationUsersResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ReadingStationUpdateUserRequest $request, User $user)
    {
        $found = ReadingStationUser::find($request->id);
        $readingStation = ReadingStation::find($request->reading_station_id);

        if (!$this->checkUserWithReadingStationAuth($readingStation, $user)) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
            ])->response()->setStatusCode(400);
        }

        if ($readingStation->table_start_number > $request->table_number || $readingStation->table_end_number < $request->table_number) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station table does not exist!']],
            ])->response()->setStatusCode(400);
        }
        if ($request->table_number !== null) {
            $foundTable = ReadingStationUser::where("reading_station_id", $request->reading_station_id)
                ->where("table_number", $request->table_number)
                ->where("user_id", '!=', $user->id)
                ->first();
            if ($foundTable) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station user table is occupied!']],
                ])->response()->setStatusCode(400);
            }
        }
        if ($found->user_id !== $user->id) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
            ])->response()->setStatusCode(400);
        }
        $found->table_number = $request->table_number;
        $found->default_package_id = $request->default_package_id;
        $found->reading_station_id = $request->reading_station_id;
        $found->user_id = $user->id;
        $found->consultant = $request->consultant;
        $found->representative = $request->representative;
        $found->contract_start = $request->contract_start;
        $found->contract_end = $request->contract_end;
        $found->save();
        return (new ReadingStationUsersResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user, $id)
    {
        $found = ReadingStationUser::where('id', $id)->where('status', 'active')->first();
        if (!$found) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station user not found!']],
            ])->response()->setStatusCode(404);
        }

        if (!$this->checkUserWithReadingStationAuth($found->readingStation, $user)) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
            ])->response()->setStatusCode(400);
        }

        if ($found->user_id !== $user->id) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station user table is not for this user!']],
            ])->response()->setStatusCode(400);
        }
        // $found->delete();
        $found->status = 'canceled';
        $found->table_number = null;
        $found->save();
        $user->is_reading_station_user = false;
        $user->save();
        return (new ReadingStationUsersResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    function bulkUpdate(ReadingStationUsersBulkUpdateRequest $request, ReadingStation $readingStation)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if (Auth::user()->reading_station_id !== $readingStation->id) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        $userStations = collect($request->data)->map(function ($data) {
            return $data['reading_station_user_id'];
        })->toArray();
        $userStationTables = collect($request->data)->map(function ($data) {
            return $data['table_number'];
        })->toArray();
        if (count($userStationTables) !== count(array_unique($userStationTables))) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station table is assigned to more than one person!']],
            ])->response()->setStatusCode(400);
        }
        $otherTables = ReadingStationUser::whereNotIn('id', $userStations)
            ->where('reading_station_id', $readingStation->id)
            ->pluck(
                'table_number'
            )->toArray();
        if (array_intersect($otherTables, $userStationTables)) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station table belongs to other!']],
            ])->response()->setStatusCode(400);
        }

        DB::beginTransaction();
        foreach ($request->data as $data) {
            DB::table('reading_station_users')->where('id', $data['reading_station_user_id'])->update(['table_number' => $data['table_number']]);
        }
        try {
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station unexpected error!']],
            ])->response()->setStatusCode(500);
        }


        return (new ReadingStationUsersResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    function addAbsentPresent(ReadingStationSetUserAbsentPresentRequest $request, ReadingStation $readingStation, User $user)
    {
        if (!$this->checkUserWithReadingStationAuth($readingStation, $user)) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
            ])->response()->setStatusCode(400);
        }

        if (!$this->hasProgram($user)) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station user does not have a plan for this week!']],
            ])->response()->setStatusCode(400);
        }

        $weeklyProgram = $this->thisWeekProgram($user);
        if ($request->reading_station_slut_user_exit_id) {
            $slut = ReadingStationSlutUser::where('reading_station_slut_id', $request->reading_station_slut_user_exit_id)
                ->where('reading_station_weekly_program_id', $weeklyProgram->id)
                ->first();
            if (!$slut) {
                $slut = new ReadingStationSlutUser();
                $slut->reading_station_weekly_program_id = $weeklyProgram->id;
                $slut->reading_station_slut_id = $request->reading_station_slut_user_exit_id;
                $slut->day = Carbon::now()->toDateString();
                $slut->is_required = 0;
                $slut->status = 'defined';
                $slut->save();
            } else {
                $slutUser = $slut->weeklyProgram->readingStationUser;
                if ($slutUser->reading_station_id !== $readingStation->id || $slutUser->user_id !== $user->id) {
                    return (new ReadingStationUsersResource(null))->additional([
                        'errors' => ['reading_station_user' => ['Reading station id does not belong to you!']],
                    ])->response()->setStatusCode(400);
                }
            }
        }


        $isProcessed = false;
        if ($request->end || $request->exit_way) {
            $isProcessed = true;
        }
        $day = Carbon::now()->toDateString();
        $absentPresent = ReadingStationAbsentPresent::where('user_id', $user->id)
            ->where('reading_station_id', $readingStation->id)
            ->where('is_processed', false)
            ->where('day', $day)
            ->first();
        if (!$absentPresent) {
            // if (!$request->enter_way) {
            //     return (new ReadingStationUsersResource(null))->additional([
            //         'errors' => ['reading_station_user' => ['You should specify the `enter_way`!']],
            //     ])->response()->setStatusCode(400);
            // }

            $absentPresent = new ReadingStationAbsentPresent();
            $absentPresent->user_id = $user->id;
            $absentPresent->reading_station_id = $readingStation->id;
            $absentPresent->day = $day;
            $absentPresent->is_optional_visit = false;
        }

        if ($absentPresent->reading_station_slut_user_exit_id && $request->possible_end) {
            $slut = ReadingStationSlutUser::where('reading_station_slut_id', $absentPresent->reading_station_slut_user_exit_id)
                ->where('reading_station_weekly_program_id', $weeklyProgram->id)
                ->first();
            $slutEnd = Carbon::parse($slut->slut->end);
            if ($slutEnd->greaterThan(Carbon::parse($request->possible_end))) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Possible End can not be before the exit slut end!']],
                ])->response()->setStatusCode(400);
            }
        }

        $absentPresent->reading_station_slut_user_exit_id = $request->exists("reading_station_slut_user_exit_id") ? $request->reading_station_slut_user_exit_id : $absentPresent->reading_station_slut_user_exit_id;
        $absentPresent->possible_end = $request->exists("possible_end") ? $request->possible_end : $absentPresent->possible_end;
        $absentPresent->end = $request->exists("end") ? $request->end : $absentPresent->end;
        $absentPresent->possible_exit_way = $request->exists("possible_exit_way") ? $request->possible_exit_way : $absentPresent->possible_exit_way;
        $absentPresent->exit_way = $request->exists("exit_way") ? $request->exit_way : $absentPresent->exit_way;
        $absentPresent->enter_way = $request->exists("enter_way") ? $request->enter_way : $absentPresent->enter_way;
        $absentPresent->is_optional_visit = $request->is_optional_visit ?? $absentPresent->is_optional_visit;
        $absentPresent->is_processed = $isProcessed;
        $absentPresent->operator_id = Auth::user()->id;
        $absentPresent->save();

        return (new ReadingStationAbsentPresentResource($absentPresent))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    function allExit(ReadingStationIndexExitsRequest $request, ReadingStation $readingStation)
    {
        $isReadingStationBranchAdmin = in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch']);
        if ($isReadingStationBranchAdmin) {
            $readingStationId = Auth::user()->reading_station_id;
            if ($readingStationId !== $readingStation->id) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        $absentPresents = ReadingStationAbsentPresent::where('reading_station_id', $readingStation->id)
            ->where('day', Carbon::now()->toDateString())
            ->where('reading_station_slut_user_exit_id', '!=', null);
        // ->where('is_processed', 0);
        if ($request->reading_station_slut_user_exit_id) {
            $absentPresents->where('reading_station_slut_user_exit_id', $request->reading_station_slut_user_exit_id);
        }

        $absentPresents = $absentPresents->get()->sort(function ($a, $b) {
            if ($a->reading_station_slut_user_exit_id && $b->reading_station_slut_user_exit_id) {
                return $a->slutUserExit->start > $b->slutUserExit->start;
            } else if ($a->reading_station_slut_user_exit_id) {
                return true;
            }
            return false;
        });

        return (new ReadingStationExitsResource($absentPresents))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    function updateExitRecord(ReadingStationChangeExitsRequest $request, ReadingStation $readingStation, ReadingStationAbsentPresent $readingStationAbsentPresent)
    {
        $isReadingStationBranchAdmin = in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch']);
        if ($isReadingStationBranchAdmin) {
            $readingStationId = Auth::user()->reading_station_id;
            if ($readingStationId !== $readingStationAbsentPresent->reading_station_id || $readingStation->id !== $readingStationId) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        if (!$request->exists('exit_delay') && !$request->exists('exit_way') && !$request->exists('exited')) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Bad Request!']],
            ])->response()->setStatusCode(400);
        }

        // if ($request->exists('exit_way') || $request->exists('exited')) {
        //     $now = str_replace('T', ' ', Carbon::now()->toDateTimeLocalString());
        //     $weeklyProgram = $this->thisWeekProgram($readingStationAbsentPresent->user);
        //     ReadingStationCall::insert([[
        //         "reading_station_absent_present_id" => $readingStationAbsentPresent->id,
        //         "reading_station_slut_user_id" => $weeklyProgram->sluts->first()->id,
        //         "reason" => "exit",
        //         "caller_user_id" => Auth::user()->id,
        //         "created_at" => $now,
        //         "updated_at" => $now,
        //     ]]);
        // }
        if ($request->exists('exited') && !$readingStationAbsentPresent->exit_way) {
            $readingStationAbsentPresent->exit_way = $readingStationAbsentPresent->possible_exit_way;
        }

        $readingStationAbsentPresent->exit_delay = $request->exists('exit_delay') ? $request->exit_delay : $readingStationAbsentPresent->exit_delay;
        $readingStationAbsentPresent->exit_way = $request->exists('exit_way') ? $request->exit_way : $readingStationAbsentPresent->exit_way;
        $readingStationAbsentPresent->is_processed = $request->exists('exited') ? $request->exited : $readingStationAbsentPresent->is_processed;
        $readingStationAbsentPresent->operator_id = Auth::user()->id;
        $readingStationAbsentPresent->save();

        return (new ReadingStationExitsResource(collect([$readingStationAbsentPresent])))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }


    function oneSmallIndex(ReadingStation $readingStation)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if (Auth::user()->reading_station_id !== $readingStation->id) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        $users = ReadingStationUser::where("reading_station_id", $readingStation->id)->orderBy('table_number')->get();

        return (new ReadingStationUsers5Collection($users))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    function absents(ReadingStationIndexUserAbsentsRequest $request, ReadingStation $readingStation)
    {
        $isReadingStationBranchAdmin = in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch']);
        if ($isReadingStationBranchAdmin) {
            $readingStationId = Auth::user()->reading_station_id;
            if ($readingStationId !== $readingStation->id) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        $slutUsers = ReadingStationSlutUser::whereHas('slut', function ($q) use ($readingStation) {
            $q->where('reading_station_id', $readingStation->id);
        })
            ->where('status', 'absent')
            ->where('is_required', 1);
        // ->where('absense_approved_status', 'not_approved');
        if ($request->exists('table_number')) {
            if ($readingStation->table_start_number > $request->table_number || $readingStation->table_end_number < $request->table_number) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station table out of range!']],
                ])->response()->setStatusCode(400);
            }
            $slutUsers->whereHas('weeklyProgram', function ($q1) use ($request) {
                $q1->whereHas('readingStationUser', function ($q2) use ($request) {
                    $q2->where('table_number', $request->table_number);
                });
            });
        }
        if ($request->exists('day')) {
            $slutUsers->where('day', $request->day);
        }

        $perPage = $request->get('per_page', null);
        $slutUsers->withAggregate('weeklyProgram', 'id')->orderBy('weekly_program_id', 'ASC');
        $slutUsers->withAggregate('slut', 'start')->orderBy('slut_start', 'ASC');

        if ($perPage === 'all') {
            return (new ReadingStationUserAbsentsCollection($slutUsers->get()))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        } else if (!$perPage) {
            $perPage = env('PAGE_COUNT');
        }

        return (new ReadingStationUserAbsentsCollection($slutUsers->paginate($perPage)))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    function absentTableNumbers(ReadingStationIndexUserAbsentTablesRequest $request, ReadingStation $readingStation)
    {
        $isReadingStationBranchAdmin = in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch']);
        if ($isReadingStationBranchAdmin) {
            $readingStationId = Auth::user()->reading_station_id;
            if ($readingStationId !== $readingStation->id) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        $slutUsers = ReadingStationSlutUser::whereHas('slut', function ($q) use ($readingStation) {
            $q->where('reading_station_id', $readingStation->id);
        })
            ->where('day', $request->day)
            ->where('status', 'absent')
            ->where('is_required', 1);
        // ->where('absense_approved_status', 'not_approved');


        return (new ReadingStationUserAbsentTablesCollection($slutUsers->get()))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function verfyAbsent(ReadingStationIndexUserAbsentVerifyRequest $request, ReadingStation $readingStation)
    {
        $readingStationSlutUserIds = explode(',', $request->reading_station_slut_user_ids);
        foreach ($readingStationSlutUserIds as $readingStationSlutUserId) {
            if (!is_numeric($readingStationSlutUserId)) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station slut user is not valid!', $readingStationSlutUserId]],
                ])->response()->setStatusCode(400);
            }
            if (!ReadingStationSlutUser::find($readingStationSlutUserId)) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station slut user is not found!', $readingStationSlutUserId]],
                ])->response()->setStatusCode(400);
            }
        }
        $isReadingStationBranchAdmin = in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch']);
        if ($isReadingStationBranchAdmin) {
            $readingStationId = Auth::user()->reading_station_id;
            if ($readingStationId !== $readingStation->id) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        foreach ($readingStationSlutUserIds as $readingStationSlutUserId) {
            $slutUser = ReadingStationSlutUser::find($readingStationSlutUserId);
            $absense_file = null;
            $file = $request->file('absense_file');
            if ($file) {
                $fileName = $file->hashName();
                $absense_file = env('FTP_PATH') . '/' . $fileName . '/' . $fileName;
                if (!$file->store(env('FTP_PATH') . '/' . $fileName, 'ftp')) {
                    return (new ReadingStationUsersResource(null))->additional([
                        'errors' => ['reading_station_user' => ['Attachment Store Error!']],
                    ])->response()->setStatusCode(500);
                }
            }
            $slutUser->absense_approved_status = $request->absense_approved_status;
            $slutUser->absense_file = $absense_file;
            $slutUser->user_id = Auth::user()->id;
            $slutUser->save();

            $strikeFixed = 0;
            $weeklyProgram = $slutUser->weeklyProgram;
            $readingStationUser = $weeklyProgram->readingStationUser;
            switch ($request->absense_approved_status) {
                case 'semi_approved':
                    $strikeFixed = 1;
                    $weeklyProgram->semi_approved_absent_day++;
                    break;

                case 'approved':
                    $strikeFixed = 2;
                    $weeklyProgram->approved_absent_day++;
                    break;
            }
            if ($strikeFixed > 0) {
                $weeklyProgram->strikes_done -= $strikeFixed;
                $weeklyProgram->save();

                $readingStationUser->total += $strikeFixed;
                $readingStationUser->save();
            }
        }
        return (new ReadingStationUsersResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    public function getVerfyAbsent(ReadingStation $readingStation, ReadingStationSlutUser $slutUser)
    {
        $isReadingStationBranchAdmin = in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch']);
        if ($isReadingStationBranchAdmin) {
            $readingStationId = Auth::user()->reading_station_id;
            if ($readingStationId !== $readingStation->id) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
            if ($slutUser->slut->reading_station_id !== $readingStation->id) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        if (!$slutUser->absense_file) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['No attachment to show!']],
            ])->response()->setStatusCode(400);
        }
        if (Storage::drive('ftp')->missing($slutUser->absense_file)) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Attachment is missing!']],
            ])->response()->setStatusCode(400);
        }
        return Storage::drive('ftp')->download($slutUser->absense_file);
    }

    function oneNoneUserIndex(ReadingStationNoneUsersIndexRequest $request, ReadingStation $readingStation)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if (Auth::user()->reading_station_id !== $readingStation->id) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }
        $sort = "id";
        $sortDir = "desc";
        $authGroup = Auth::user()->group;
        $paginatedReadingStationOffdays = User::where('reading_station_id', $readingStation->id)
            ->where(function ($q) use ($request, $authGroup) {
                if ($request->get('name')) {
                    $name = $request->get('name');
                    $q->where(DB::raw("CONCAT(first_name, ' ',last_name)"), 'like', '%' . $name . '%');
                }
                if ($request->get('phone')) {
                    $phone = $request->get('phone');
                    $q->where('email', 'like', '%' . $phone . '%');
                }
                switch ($authGroup->type) {
                    case 'admin_reading_station_branch':
                        $q->whereHas('group', function ($q1) {
                            $q1->where('type', 'user_reading_station_branch');
                        });

                        break;
                    case 'admin_reading_station':
                        $q->whereHas('group', function ($q1) {
                            $q1->whereIn('type', ['user_reading_station_branch', 'admin_reading_station_branch']);
                        });

                        break;
                }
            });
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sortDir = $request->get('sort_dir');
        }
        if ($request->get('per_page') == "all") {
            $paginatedReadingStationOffdays = $paginatedReadingStationOffdays->orderBy($sort, $sortDir)->get();
        } else {
            $perPage = $request->get('per_page');
            if (!$perPage) {
                $perPage = env('PAGE_COUNT');
            }
            $paginatedReadingStationOffdays = $paginatedReadingStationOffdays->orderBy($sort, $sortDir)->paginate($perPage);
        }
        return (new NoneUserCollection($paginatedReadingStationOffdays))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    private function checkUserWithReadingStationAuth(ReadingStation $readingStation, User $user): bool
    {
        return ReadingStationAuth::checkUserWithReadingStationAuth($readingStation, $user);
    }

    private function hasProgram(User $user): bool
    {
        $date = Carbon::now();
        if ($user->readingStationUser && $user->readingStationUser->weeklyPrograms && count($user->readingStationUser->weeklyPrograms)) {
            $weeklyProgram = $user->readingStationUser->weeklyPrograms->where('end', $date->endOfWeek(Carbon::FRIDAY)->toDateString())->first();
            if ($weeklyProgram) {
                return true;
            }
        }

        return false;
    }


    private function thisWeekProgram(User $user): ReadingStationWeeklyProgram
    {
        $date = Carbon::now();
        return $user->readingStationUser->weeklyPrograms->where('end', $date->endOfWeek(Carbon::FRIDAY)->toDateString())->first();
    }
}
