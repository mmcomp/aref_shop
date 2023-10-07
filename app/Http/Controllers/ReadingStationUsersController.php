<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationCreateUserRequest;
use App\Http\Requests\ReadingStationUpdateUserRequest;
use App\Http\Requests\ReadingStationUsersIndexRequest;
use App\Http\Requests\UserIndexRequest;
use App\Http\Resources\ReadingStationUsers2Collection;
use App\Http\Resources\ReadingStationUsersCollection;
use App\Http\Resources\ReadingStationUsersResource;
use App\Models\ReadingStation;
use App\Models\ReadingStationUser;
use App\Models\User;
use Illuminate\Http\Request;

class ReadingStationUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(UserIndexRequest $request)
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
        ReadingStationUser::create([
            "reading_station_id" => $request->reading_station_id,
            "user_id" => $user->id,
            "table_number" => $request->table_number,
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
            $found->user_id === $user->id
        ) {
            // no changes
            return (new ReadingStationUsersResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(201);
        }
        if ($request->table_number !== null) {
            $foundTable = ReadingStationUser::where("reading_station_id", $request->reading_station_id)->where("table_number", $request->table_number)->first();
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
