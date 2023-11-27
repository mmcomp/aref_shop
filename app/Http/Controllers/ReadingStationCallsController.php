<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationCreateRequest;
use App\Http\Requests\ReadingStationIndexRequest;
use App\Http\Requests\ReadingStationUpdateRequest;
use App\Http\Resources\ReadingStation2Collection;
use App\Http\Resources\ReadingStationResource;
use App\Models\ReadingStation;
use App\Models\ReadingStationUser;
use App\Models\User;
use App\Utils\ReadingStationSms;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Utils\ReadingStationAuth;
use App\Http\Resources\ReadingStationAllCallsResource;
use App\Models\ReadingStationSlutUser;
use Carbon\Carbon;

class ReadingStationCallsController extends Controller
{

    function index(ReadingStationIndexRequest $request, ReadingStation $readingStation)
    {
        if (!$this->checkUserWithReadingStationAuth($readingStation)) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station_call' => ['Reading station call are not available for you!']],
            ])->response()->setStatusCode(400);
        }
        $today = Carbon::now()->toDateString();
        $weeklyPrograms = [];
        $readingStation->users->map(function ($user) use (&$weeklyPrograms) {
            return $user->weeklyPrograms->map(function ($weeklyProgram) use (&$weeklyPrograms) {
                $weeklyPrograms[] = $weeklyProgram->id;
            });
        })->toArray();
        $todaySluts = ReadingStationSlutUser::whereIn('reading_station_weekly_program_id', $weeklyPrograms)
            ->where('day', $today)
            ->get();
        $userSluts = [];
        $todaySluts->map(function ($slutUser) use (&$userSluts) {
            if (isset($userSluts[$slutUser->reading_station_weekly_program_id])) {
                $currentSlut = $userSluts[$slutUser->reading_station_weekly_program_id];
                if (Carbon::parse($currentSlut->slut->start)->isBefore(Carbon::parse($slutUser->slut->start))) {
                    return;
                }
            }
            $userSluts[$slutUser->reading_station_weekly_program_id] = $slutUser;
        });
        return (new ReadingStationAllCallsResource($userSluts))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
        // return ($userSluts);
        // $isReadingStationBranchAdmin = Auth::user()->group->type === 'admin_reading_station_branch';
        // $sort = "id";
        // $sortDir = "desc";
        // $paginatedReadingStations = [];
        // if ($request->get('sort_dir') != null && $request->get('sort') != null) {
        //     $sort = $request->get('sort');
        //     $sortDir = $request->get('sort_dir'); 
        // }
        // $paginatedReadingStations = ReadingStation::where('id', '>', 0);
        // if ($isReadingStationBranchAdmin) {
        //     $readingStationId = Auth::user()->reading_station_id;
        //     if ($readingStationId === null) {
        //         return (new ReadingStation2Collection(null))->additional([
        //             'errors' => null,
        //         ])->response()->setStatusCode(200);
        //     }
        //     $paginatedReadingStations->where('id', $readingStationId);
        // }
        // if ($request->get('per_page') == "all") {
        //     $paginatedReadingStations = $paginatedReadingStations->orderBy($sort, $sortDir)->get();
        // } else {
        //     $perPage = $request->get('per_page');
        //     if (!$perPage) {
        //         $perPage = env('PAGE_COUNT');
        //     }
        //     $paginatedReadingStations = $paginatedReadingStations->orderBy($sort, $sortDir)->paginate($perPage);
        // }
        // return (new ReadingStation2Collection($paginatedReadingStations))->additional([
        //     'errors' => null,
        // ])->response()->setStatusCode(200);
    }

    /*
    function store(ReadingStationCreateRequest $request)
    {
        if ($request->table_start_number > $request->table_end_number) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station' => ['Reading station start table number should be less or equal end table number!']],
            ])->response()->setStatusCode(400);
        }
        ReadingStation::create(["name" => $request->name, "table_start_number" => $request->table_start_number, "table_end_number" => $request->table_end_number]);
        return (new ReadingStationResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    function update(ReadingStationUpdateRequest $request)
    {
        $readingStation = ReadingStation::find($request->id);
        if (!$readingStation) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station' => ['Reading station not found!']],
            ])->response()->setStatusCode(404);
        }
        if ($request->table_start_number > $request->table_end_number) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station' => ['Reading station start table number should be less or equal end table number!']],
            ])->response()->setStatusCode(400);
        }
        if ($request->name) {
            if ($request->name !== $readingStation->name) {
                $found = ReadingStation::where("name", $request->name)->first();
                if ($found) {
                    return (new ReadingStationResource(null))->additional([
                        'errors' => ['reading_station' => ['Reading station with the same name exists!']],
                    ])->response()->setStatusCode(400);
                }
            }
            $readingStation->name = $request->name;
        }
        if ($request->table_start_number) {
            $readingStation->table_start_number = $request->table_start_number;
        }
        if ($request->table_end_number) {
            $readingStation->table_end_number = $request->table_end_number;
        }
        $readingStation->save();
        return (new ReadingStationResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    public function destroy(ReadingStation $readingStation)
    {
        if (count($readingStation->users) !== 0) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station' => ['Reading station has users!']],
            ])->response()->setStatusCode(400);
        }
        $readingStation->delete();
        return (new ReadingStationResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    public function findOne(ReadingStation $readingStation)
    {
        if (Auth::user()->group->type === 'admin_reading_station_branch') {
            if (Auth::user()->reading_station_id !== $readingStation->id) {
                return (new ReadingStationResource(null))->additional([
                    'errors' => ['reading_station' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);            }
        }
        $availableTables = $this->availableTables($readingStation);
        $readingStation->availableTables = $availableTables;
        return (new ReadingStationResource($readingStation))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
    */


    private function checkUserWithReadingStationAuth(ReadingStation $readingStation): bool
    {
        return ReadingStationAuth::checkUserWithReadingStationAuth($readingStation);
    }
}
