<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationStrikesCreateRequest;
use App\Http\Requests\ReadingStationStrikesIndexRequest;
use App\Http\Requests\ReadingStationStrikesUpdateRequest;
use App\Http\Resources\ReadingStationStrikesCollection;
use App\Http\Resources\ReadingStationStrikesResource;
use App\Models\ReadingStationStrike;

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

    function index(ReadingStationStrikesIndexRequest $request) {
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
}
