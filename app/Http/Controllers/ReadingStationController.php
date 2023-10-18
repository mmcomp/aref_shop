<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationCreateRequest;
use App\Http\Requests\ReadingStationIndexRequest;
use App\Http\Requests\ReadingStationUpdateRequest;
use App\Http\Resources\ReadingStation2Collection;
use App\Http\Resources\ReadingStationResource;
use App\Models\ReadingStation;
use App\Utils\ReadingStationSms;

class ReadingStationController extends Controller
{
    public function __construct(
        protected ReadingStationSms $smsProvider,
    ) {
    }

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

    function index(ReadingStationIndexRequest $request)
    {
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
        return (new ReadingStation2Collection($paginatedReadingStations))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function findOne(ReadingStation $readingStation)
    {
        return (new ReadingStationResource($readingStation))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function testSms()
    {
        return $this->smsProvider->send(['09155193104', '09153068145'], ['پیام تستی']);
    }
}
