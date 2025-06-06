<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationCreateStrikesRequest;
use App\Http\Requests\ReadingStationIndexStrikesRequest;
use App\Http\Requests\ReadingStationStrikesCreateRequest;
use App\Http\Requests\ReadingStationStrikesIndexRequest;
use App\Http\Requests\ReadingStationStrikesUpdateRequest;
use App\Http\Requests\ReadingStationUserAbsentsIndexRequest;
use App\Http\Resources\ReadingStationSlutUserWeeklyProgramCollection;
use App\Http\Resources\ReadingStationStrikesCollection;
use App\Http\Resources\ReadingStationStrikesResource;
use App\Http\Resources\ReadingStationUserStrikesCollection;
use App\Models\ReadingStation;
use App\Models\ReadingStationSlutUser;
use App\Models\ReadingStationStrike;
use App\Models\ReadingStationUserStrike;
use App\Models\ReadingStationWeeklyProgram;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReadingStationStrikeController extends Controller
{
    function store(ReadingStationStrikesCreateRequest $request)
    {
        $found = ReadingStationStrike::where("name", $request->name)->first();
        if ($found) {
            return (new ReadingStationStrikesResource(null))->additional([
                'errors' => ['reading_station_strike' => ['Reading station strike with this name exists!']],
            ])->response()->setStatusCode(400);
        }
        $isPoint = false;
        if ($request->exists('is_point')) {
            $isPoint = $request->is_point;
        }
        ReadingStationStrike::create([
            "name" => $request->name,
            "score" => $request->score,
            "is_point" => $isPoint,
        ]);
        return (new ReadingStationStrikesResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    function update(ReadingStationStrikesUpdateRequest $request)
    {
        $readingStationStrike = ReadingStationStrike::find($request->id);
        if ($request->exists('name')) {
            if ($request->name !== $readingStationStrike->name) {
                $found = ReadingStationStrike::where("name", $request->name)->first();
                if ($found) {
                    return (new ReadingStationStrikesResource(null))->additional([
                        'errors' => ['reading_station_strike' => ['Reading station strike with this name exists!']],
                    ])->response()->setStatusCode(400);
                }
            }
            $readingStationStrike->name = $request->name;
        }
        if ($request->exists('score')) {
            $readingStationStrike->score = $request->score;
        }
        if ($request->exists('is_point')) {
            $readingStationStrike->is_point = $request->is_point;
        }

        $readingStationStrike->save();
        return (new ReadingStationStrikesResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    public function destroy(ReadingStationStrike $readingStationStrike)
    {
        $readingStationStrike->delete();
        return (new ReadingStationStrikesResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    function index(ReadingStationStrikesIndexRequest $request)
    {
        $sort = "id";
        $sortDir = "desc";
        $paginatedReadingStations = [];
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sortDir = $request->get('sort_dir');
        }
        $paginatedReadingStations = ReadingStationStrike::where('id', '>', 0);
        if ($request->exists('is_point')) {
            $paginatedReadingStations->where('is_point', $request->is_point === 'true');
        }
        if ($request->get('per_page') == "all") {
            $paginatedReadingStations = $paginatedReadingStations->orderBy($sort, $sortDir)->get();
        } else {
            $perPage = $request->get('per_page');
            if (!$perPage) {
                $perPage = env('PAGE_COUNT');
            }
            $paginatedReadingStations = $paginatedReadingStations->orderBy($sort, $sortDir)->paginate($perPage);
        }
        return (new ReadingStationStrikesCollection($paginatedReadingStations))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function findOne(ReadingStationStrike $readingStationStrike)
    {
        return (new ReadingStationStrikesResource($readingStationStrike))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function readingStationIndex(ReadingStationIndexStrikesRequest $request, ReadingStation $readingStation)
    {
        $isReadingStationBranchAdmin = in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch']);
        if ($isReadingStationBranchAdmin) {
            $readingStationId = Auth::user()->reading_station_id;
            if ($readingStationId !== $readingStation->id) {
                return (new ReadingStationStrikesResource(null))->additional([
                    'errors' => ['reading_station_strike' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        $sort = "id";
        $sortDir = "desc";
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sortDir = $request->get('sort_dir');
        }


        $day = $request->exists('day') ? $request->day : Carbon::now()->toDateString();
        $strikes = ReadingStationUserStrike::whereDate('updated_at', $day)->whereHas('readingStationSlutUser', function ($query) use ($readingStation) {
            $query->whereHas('slut', function ($q) use ($readingStation) {
                $q->where('reading_station_id', $readingStation->id);
            });
        });
        if ($request->exists('reading_station_strike_id')) {
            $strikes->where('reading_station_strike_id', $request->reading_station_strike_id);
        }
        $total = 0;
        $all = $strikes->get();
        $all->map(function ($strike) use (&$total) {
            $total += ($strike->readingStationStrike->is_point ? 1 : -1) * $strike->reading_station_strike_score;
        });
        $strikes->orderBy($sort, $sortDir);
        if ($request->get('per_page') == "all") {
            $strikes = $strikes->get();
        } else {
            $perPage = $request->get('per_page');
            if (!$perPage) {
                $perPage = env('PAGE_COUNT');
            }

            $strikes = $strikes->paginate($perPage);
        }

        return (new ReadingStationUserStrikesCollection($strikes, $total))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function addReadingStationUserStrike(ReadingStationCreateStrikesRequest $request, ReadingStation $readingStation)
    {
        $readingStationId = Auth::user()->reading_station_id;
        $isReadingStationBranchAdmin = in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch']);
        if ($isReadingStationBranchAdmin) {
            if ($readingStationId !== $readingStation->id) {
                return (new ReadingStationStrikesResource(null))->additional([
                    'errors' => ['reading_station_strike' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }
        $user = User::find($request->user_id);
        if ($user->readingStationUser->reading_station_id !== $readingStation->id) {
            return (new ReadingStationStrikesResource(null))->additional([
                'errors' => ['reading_station_strike' => ['Reading station does not belong to the User!']],
            ])->response()->setStatusCode(400);
        }
        $weeklyProgram = ReadingStationWeeklyProgram::where('reading_station_user_id', $user->readingStationUser->id)
            ->whereDate('start', '<=', $request->day)
            ->whereDate('end', '>=', $request->day)
            ->first();
        if (!$weeklyProgram) {
            return (new ReadingStationStrikesResource(null))->additional([
                'errors' => ['reading_station_strike' => ['User does not have a program for that week!']],
            ])->response()->setStatusCode(400);
        }
        $slutUser = ReadingStationSlutUser::where('reading_station_weekly_program_id', $weeklyProgram->id)
            ->where('reading_station_slut_id', $request->reading_station_slut_id)
            ->where('day', $request->day)
            ->where('status', '!=', 'defined')
            ->where('status', '!=', 'absent')
            ->first();
        // $weeklyPrograms = $user->readingStationUser->weeklyPrograms;
        // $weeklyPrograms->map(function ($weeklyProgram) use (&$slutUser, $request) {
        //     $slutUser = $weeklyProgram->sluts
        //         ->where('reading_station_slut_id', $request->reading_station_slut_id)
        //         ->where('day', $request->day)
        //         ->where('status', '!=', 'defined')
        //         ->where('status', '!=', 'absent')
        //         ->first();
        // });
        if (!$slutUser) {
            return (new ReadingStationStrikesResource(null))->additional([
                'errors' => ['reading_station_strike' => ['User does not have any records for this slut!']],
            ])->response()->setStatusCode(400);
        }

        $strike = ReadingStationStrike::find($request->reading_station_strike_id);

        $userStrike = new ReadingStationUserStrike();
        $userStrike->reading_station_slut_user_id = $slutUser->id;
        $userStrike->reading_station_strike_id = $request->reading_station_strike_id;
        $userStrike->reading_station_strike_score = $request->exists('reading_station_strike_score') ? $request->reading_station_strike_score : $strike->score;
        $userStrike->description = $request->description;
        $userStrike->day = $request->day;
        $userStrike->save();

        $weeklyProgram =  $slutUser->weeklyProgram;
        $weeklyProgram->strikes_done += ($strike->is_point ? -1 : 1) * $userStrike->reading_station_strike_score;
        $weeklyProgram->save();

        $readingStationUser = $weeklyProgram->readingStationUser;
        $readingStationUser->total -= ($strike->is_point ? -1 : 1) *  $userStrike->reading_station_strike_score;
        $readingStationUser->save();

        return (new ReadingStationStrikesResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    public function strikes(ReadingStationUserAbsentsIndexRequest $request, User $user)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if ($user->readingStationUser->readingStation->id !== Auth::user()->reading_station_id) {
                return (new ReadingStationStrikesResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        $strikes = ReadingStationUserStrike::whereHas('readingStationSlutUser', function ($q1) use ($user) {
            $q1->whereHas('weeklyProgram', function ($q2) use ($user) {
                $q2->whereHas('readingStationUser', function ($q3) use ($user) {
                    $q3->whereHas('user', function ($q4) use ($user) {
                        $q4->where('id', $user->id);
                    });
                });
            });
        });
        $total = 0;
        $all = $strikes->get();
        $all->map(function ($strike) use (&$total) {
            $total += ($strike->readingStationStrike->is_point ? 1 : -1) * $strike->reading_station_strike_score;
        });

        $sort = "day";
        $sortDir = "desc";

        $strikes->orderBy($sort, $sortDir);
        $perPage = $request->per_page;
        if (!$perPage) {
            $perPage = env('PAGE_COUNT');
        }
        if ($request->per_page === 'all') {
            $output = $strikes->get();
        } else {
            $output = $strikes->paginate($perPage);
        }

        return (new ReadingStationUserStrikesCollection($output, $total))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function strikesUser(ReadingStationUserAbsentsIndexRequest $request, User $user)
    {
        if (Auth::user()->id !== $user->id) {
            return (new ReadingStationStrikesResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
            ])->response()->setStatusCode(400);
        }

        $strikes = ReadingStationUserStrike::whereHas('readingStationSlutUser', function ($q1) use ($user) {
            $q1->whereHas('weeklyProgram', function ($q2) use ($user) {
                $q2->whereHas('readingStationUser', function ($q3) use ($user) {
                    $q3->whereHas('user', function ($q4) use ($user) {
                        $q4->where('id', $user->id);
                    });
                });
            });
        });
        $total = 0;
        $all = $strikes->get();
        $all->map(function ($strike) use (&$total) {
            $total += ($strike->readingStationStrike->is_point ? 1 : -1) * $strike->reading_station_strike_score;
        });

        $sort = "day";
        $sortDir = "desc";

        $strikes->orderBy($sort, $sortDir);
        $perPage = $request->per_page;
        if (!$perPage) {
            $perPage = env('PAGE_COUNT');
        }
        if ($request->per_page === 'all') {
            $output = $strikes->get();
        } else {
            $output = $strikes->paginate($perPage);
        }

        return (new ReadingStationUserStrikesCollection($output, $total))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
