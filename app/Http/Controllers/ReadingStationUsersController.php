<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationCreateUserRequest;
use App\Http\Requests\ReadingStationUpdateUserRequest;
use App\Http\Requests\ReadingStationUsersIndexRequest;
use App\Http\Resources\ReadingStationUsers2Collection;
use App\Http\Resources\ReadingStationUsersCollection;
use App\Http\Resources\ReadingStationUsersResource;
use App\Models\ReadingStation;
use App\Models\ReadingStationPackage;
use App\Models\ReadingStationUser;
use App\Models\ReadingStationWeeklyProgram;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReadingStationUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ReadingStationUsersIndexRequest $request)
    {
        $sort = "id";
        $sortDir = "desc";
        $paginatedReadingStationOffdays = [];
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sortDir = $request->get('sort_dir');
        }
        if ($request->get('per_page') == "all") {
            $paginatedReadingStationOffdays = ReadingStationUser::orderBy($sort, $sortDir)->with('readingStation')->get();
        } else {
            $perPage = $request->get('per_page');
            if (!$perPage) {
                $perPage = env('PAGE_COUNT');
            }
            $paginatedReadingStationOffdays = ReadingStationUser::orderBy($sort, $sortDir)->paginate($perPage);
        }
        return (new ReadingStationUsersCollection($paginatedReadingStationOffdays))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    function oneIndex(ReadingStationUsersIndexRequest $request, ReadingStation $readingStation)
    {
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ReadingStationCreateUserRequest $request, User $user)
    {
        $readingStation = ReadingStation::find($request->reading_station_id);
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
            // change user
            $user->is_reading_station_user = true;
            $user->save();
            $oldUser = User::find($found->user_id);
            $oldUser->is_reading_station_user = false;
            $user->save();
        }
        $found->table_number = $request->table_number;
        $found->default_package_id = $request->default_package_id;
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
        if ($found->user_id !== $user->id) {
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station user table is not for this user!']],
            ])->response()->setStatusCode(400);
        }
        $found->delete();
        return (new ReadingStationUsersResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }
}
