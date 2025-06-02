<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationOffdaysCreateRequest;
use App\Http\Requests\ReadingStationOffdaysIndexRequest;
use App\Http\Resources\ReadingStationOffdays2Collection;
use App\Http\Resources\ReadingStationOffdaysCollection;
use App\Http\Resources\ReadingStationOffdaysResource;
use App\Models\ReadingStation;
use App\Models\ReadingStationOffday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReadingStationOffdaysController extends Controller
{
    function store(ReadingStationOffdaysCreateRequest $request, ReadingStation $readingStation)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            return (new ReadingStationOffdaysResource(null))->additional([
                'errors' => ['reading_station_user' => ['Access Denied!']],
            ])->response()->setStatusCode(403);
        }

        $offday = Carbon::parse($request->offday)->toDateString();
        $found = ReadingStationOffday::where([["offday", $offday], ["reading_station_id", $readingStation->id]])->first();
        if ($found) {
            return (new ReadingStationOffdaysResource(null))->additional([
                'errors' => ['reading_station_offday' => ['Reading station offday exists!']],
            ])->response()->setStatusCode(400);
        }
        ReadingStationOffday::create(["reading_station_id" => $readingStation->id, "offday" => $offday]);
        return (new ReadingStationOffdaysResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }


    public function destroy($id)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            return (new ReadingStationOffdaysResource(null))->additional([
                'errors' => ['reading_station_user' => ['Access Denied!']],
            ])->response()->setStatusCode(403);
        }
        ReadingStationOffday::find($id)->delete();
        return (new ReadingStationOffdaysResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }


    function index(ReadingStationOffdaysIndexRequest $request)
    {
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            return (new ReadingStationOffdaysResource(null))->additional([
                'errors' => ['reading_station_user' => ['Access Denied!']],
            ])->response()->setStatusCode(403);
        }
        $sort = "id";
        $sortDir = "desc";
        $paginatedReadingStationOffdays = [];
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sortDir = $request->get('sort_dir');
        }
        if ($request->get('per_page') == "all") {
            $paginatedReadingStationOffdays = ReadingStationOffday::orderBy($sort, $sortDir)->with('readingStation')->get();
        } else {
            $perPage = $request->get('per_page');
            if (!$perPage) {
                $perPage = env('PAGE_COUNT');
            }
            $paginatedReadingStationOffdays = ReadingStationOffday::orderBy($sort, $sortDir)->with('readingStation')->paginate($perPage);
        }
        return (new ReadingStationOffdaysCollection($paginatedReadingStationOffdays))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    function oneIndex(ReadingStationOffdaysIndexRequest $request, ReadingStation $readingStation)
    {
        $sort = "id";
        $sortDir = "desc";
        $paginatedReadingStationOffdays = ReadingStationOffday::where("reading_station_id", $readingStation->id);
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
        return (new ReadingStationOffdays2Collection($paginatedReadingStationOffdays))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
