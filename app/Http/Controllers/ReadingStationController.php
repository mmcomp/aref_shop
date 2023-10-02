<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationCreateRequest;
use App\Http\Requests\ReadingStationUpdateRequest;
use App\Http\Requests\UserIndexRequest;
use App\Http\Resources\ReadingStationCollection;
use App\Http\Resources\ReadingStationResource;
use App\Models\ReadingStation;

class ReadingStationController extends Controller
{
    function store(ReadingStationCreateRequest $request)
    {
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
        if ($request->name) {
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

    public function destroy($id)
    {
        ReadingStation::find($id)->delete();
        return (new ReadingStationResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    function index(UserIndexRequest $request) {
        $sort = "id";
        $sortDir = "desc";
        $paginatedReadingStations = [];
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sortDir = $request->get('sort_dir');
        }
        if ($request->get('per_page') == "all") {
            $paginatedReadingStations = ReadingStation::orderBy($sort, $sortDir)->get();
        } else {
            $perPage = $request->get('per_page');
            if (!$perPage) {
                $perPage = env('PAGE_COUNT');
            }
            $paginatedReadingStations = ReadingStation::orderBy($sort, $sortDir)->paginate($perPage);
        }
        return (new ReadingStationCollection($paginatedReadingStations))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function findOne($id)
    {
        $found = ReadingStation::where("id", $id)->with('offdays')->first();
        if (!$found) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station' => ['Reading station not found!']],
            ])->response()->setStatusCode(404);
        }
        return (new ReadingStationResource($found))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
