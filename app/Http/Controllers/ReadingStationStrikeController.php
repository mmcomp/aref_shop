<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationIndexStrikesRequest;
use App\Http\Requests\ReadingStationStrikesCreateRequest;
use App\Http\Requests\ReadingStationStrikesIndexRequest;
use App\Http\Requests\ReadingStationStrikesUpdateRequest;
use App\Http\Resources\ReadingStationStrikesCollection;
use App\Http\Resources\ReadingStationStrikesResource;
use App\Http\Resources\ReadingStationUserStrikesCollection;
use App\Http\Resources\ReadingStationUserStrikesResource;
use App\Models\ReadingStation;
use App\Models\ReadingStationStrike;
use App\Models\ReadingStationUserStrike;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
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
        ReadingStationStrike::create(["name" => $request->name, "score" => $request->score]);
        return (new ReadingStationStrikesResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    function update(ReadingStationStrikesUpdateRequest $request)
    {
        $readingStationStrike = ReadingStationStrike::find($request->id);
        if ($request->name) {
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
        if ($request->score) {
            $readingStationStrike->score = $request->score;
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
        if ($request->get('per_page') == "all") {
            $paginatedReadingStations = ReadingStationStrike::orderBy($sort, $sortDir)->get();
        } else {
            $perPage = $request->get('per_page');
            if (!$perPage) {
                $perPage = env('PAGE_COUNT');
            }
            $paginatedReadingStations = ReadingStationStrike::orderBy($sort, $sortDir)->paginate($perPage);
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
        $isReadingStationBranchAdmin = Auth::user()->group->type === 'admin_reading_station_branch';
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

        return (new ReadingStationUserStrikesCollection($strikes))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
