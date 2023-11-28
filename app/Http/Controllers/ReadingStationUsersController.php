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

class ReadingStationUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ReadingStationUsersIndexRequest $request)
    {
        $isReadingStationBranchAdmin = Auth::user()->group->type === 'admin_reading_station_branch';
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

    function oneIndex(ReadingStationUsersIndexRequest $request, ReadingStation $readingStation)
    {
        if (Auth::user()->group->type === 'admin_reading_station_branch') {
            if (Auth::user()->reading_station_id !== $readingStation->id) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }
        $sort = "id";
        $sortDir = "desc";
        $paginatedReadingStationOffdays = ReadingStationUser::where("reading_station_id", $readingStation->id);
        if ($request->get('name') || $request->get('phone')) {
            $paginatedReadingStationOffdays
                ->whereHas('user', function ($q) use ($request) {
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
                });
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
        $isReadingStationBranchAdmin = Auth::user()->group->type === 'admin_reading_station_branch';
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

        return (new ReadingStationUserSlutsResource($readingStation->users, $slut))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function setUserSlutStatus(ReadingStationSetUserSlutStatusRequest $request, ReadingStation $readingStation, User $user, ReadingStationSlut $slut)
    {
        if (!$this->checkUserWithReadingStationAuth($readingStation, $user)) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
            ])->response()->setStatusCode(400);
        }

        $userSlut = new ReadingStationSlutUser();
        $today = Carbon::now()->toDateString();
        foreach ($user->readingStationUser->weeklyPrograms as $weeklyProgram) {
            foreach ($weeklyProgram->sluts as $_slut) {
                if ($_slut->reading_station_slut_id === $slut->id && $_slut->day === $today) {
                    $userSlut = $_slut;
                    break;
                }
            }
        }
        if (!$userSlut->id) {
            if ($request->status === 'absent' || $request->status === 'defined') {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station user does not have a plan for this week!']],
                ])->response()->setStatusCode(400);
            }
            $thisWeeklyProgram = null;
            foreach ($user->readingStationUser->weeklyPrograms as $weeklyProgram) {
                if (Carbon::now()->between(Carbon::parse($weeklyProgram->start), Carbon::parse($weeklyProgram->end), true)) {
                    $thisWeeklyProgram = $weeklyProgram;
                    break;
                }
            }

            if (!$thisWeeklyProgram || ($thisWeeklyProgram && count($thisWeeklyProgram->sluts) === 0)) {
                return (new ReadingStationUsersResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station user does not have a plan for this week!']],
                ])->response()->setStatusCode(400);
            }
            $userSlut->reading_station_weekly_program_id = $thisWeeklyProgram->id;
            $userSlut->reading_station_slut_id = $slut->id;
            $userSlut->day = $today;
            $userSlut->is_required = false;
        }
        $userSlut->status = $request->status;
        $userSlut->save();

        return (new ReadingStationUsersResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
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
        if (!$requiredTime) {
            $requiredTime = $package->required_time;
        }
        $optionalTime = $request->optional_time;
        if (!$optionalTime) {
            $optionalTime = $package->optional_time;
        }
        $start = $date->toDateString();
        $end = $date->endOfWeek(Carbon::FRIDAY)->toDateString();


        $id = ReadingStationUser::create([
            "reading_station_id" => $request->reading_station_id,
            "user_id" => $user->id,
            "table_number" => $request->table_number,
            "default_package_id" => $request->default_package_id,
        ])->id;
        ReadingStationWeeklyProgram::create([
            "reading_station_user_id" => $id,
            "name" => $package->name,
            "start" => $start,
            "end" => $end,
            "required_time" => $requiredTime,
            "optional_time" => $optionalTime,
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
        if (
            $found->table_number === $request->table_number &&
            $found->reading_station_id === $request->reading_station_id &&
            $found->default_package_id === $request->default_package_id &&
            $found->user_id === $user->id
        ) {
            // no changes
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(201);
        }
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
        $found = ReadingStationUser::find($id);
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
        $found->delete();
        $user->is_reading_station_user = false;
        $user->save();
        return (new ReadingStationUsersResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    function bulkUpdate(ReadingStationUsersBulkUpdateRequest $request, ReadingStation $readingStation)
    {
        if (Auth::user()->group->type === 'admin_reading_station_branch') {
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
        $otherTables = ReadingStationUser::whereNotIn('id', $userStations)->pluck('table_number')->toArray();
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

        if ($request->reading_station_slut_user_exit_id) {
            $weeklyProgram = $this->thisWeekProgram($user);
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

            $slutEnd = Carbon::parse($slut->slut->end);
            if ($request->possible_end) {
                if ($slutEnd->greaterThan(Carbon::parse($request->possible_end))) {
                    return (new ReadingStationUsersResource(null))->additional([
                        'errors' => ['reading_station_user' => ['Possible End can not be before the exit slut end!']],
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
        $absentPresent->reading_station_slut_user_exit_id = $request->reading_station_slut_user_exit_id ?? $absentPresent->reading_station_slut_user_exit_id;
        $absentPresent->possible_end = $request->exists("possible_end") ? $request->possible_end : $absentPresent->possible_end;
        $absentPresent->end = $request->exists("end") ? $request->end : $absentPresent->end;
        $absentPresent->posssible_exit_way = $request->exists("posssible_exit_way") ? $request->posssible_exit_way : $absentPresent->posssible_exit_way;
        $absentPresent->exit_way = $request->exists("exit_way") ? $request->exit_way : $absentPresent->exit_way;
        $absentPresent->enter_way = $request->exists("enter_way") ? $request->enter_way : $absentPresent->enter_way;
        $absentPresent->is_optional_visit = $request->is_optional_visit ?? $absentPresent->is_optional_visit;
        $absentPresent->is_processed = $isProcessed;
        $absentPresent->save();

        return (new ReadingStationAbsentPresentResource($absentPresent))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
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
