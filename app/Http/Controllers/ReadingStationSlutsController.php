<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ReadingStationAuth;
use App\Http\Requests\ReadingStationSlutsCreateRequest;
use App\Http\Requests\ReadingStationSlutsIndexRequest;
use App\Http\Resources\ReadingStationSluts2Collection;
use App\Http\Resources\ReadingStationSlutsCollection;
use App\Http\Resources\ReadingStationSlutsResource;
use App\Models\ReadingStation;
use App\Models\ReadingStationSlut;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReadingStationSlutsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ReadingStationSlutsIndexRequest $request) {
        $sort = "id";
        $sortDir = "desc";
        $paginatedReadingStationOffdays = [];
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sortDir = $request->get('sort_dir');
        }
        if ($request->get('per_page') == "all") {
            $paginatedReadingStationOffdays = ReadingStationSlut::orderBy($sort, $sortDir)->get();
        } else {
            $perPage = $request->get('per_page');
            if (!$perPage) {
                $perPage = env('PAGE_COUNT');
            }
            $paginatedReadingStationOffdays = ReadingStationSlut::orderBy($sort, $sortDir)->paginate($perPage);
        }
        return (new ReadingStationSlutsCollection($paginatedReadingStationOffdays))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }


    function oneIndex(ReadingStationSlutsIndexRequest $request, ReadingStation $readingStation)
    {      
        if (in_array(Auth::user()->group->type, ['admin_reading_station_branch', 'user_reading_station_branch'])) {
            if (Auth::user()->reading_station_id !== $readingStation->id) {
                return (new ReadingStationSlutsResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station does not belong to you!']],
                ])->response()->setStatusCode(400);
            }
        }

        $sort = "id";
        $sortDir = "desc";
        $paginatedReadingStationOffdays = ReadingStationSlut::where("reading_station_id", $readingStation->id);
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
        return (new ReadingStationSluts2Collection($paginatedReadingStationOffdays))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ReadingStationSlutsCreateRequest $request, ReadingStation $readingStation)
    {
        $duration = Carbon::parse($request->end)->diffInMinutes(Carbon::parse($request->start));
        $found = ReadingStationSlut::where([
            ["name", $request->name], 
            ["start", $request->start], 
            ["end", $request->end],
            ["reading_station_id", $readingStation->id],
        ])->first();
        if ($found) {
            return (new ReadingStationSlutsResource(null))->additional([
                'errors' => ['reading_station_slut' => ['Reading station slut exists!']],
            ])->response()->setStatusCode(400);
        }
        ReadingStationSlut::create([
            "reading_station_id" => $readingStation->id, 
            "name" => $request->name, 
            "start" => $request->start, 
            "end" => $request->end, 
            "duration" => $duration,
            "is_sleep" => $request->exists('is_sleep') ? $request->is_sleep : false,
        ]);
        return (new ReadingStationSlutsResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ReadingStationSlut::find($id)->delete();
        return (new ReadingStationSlutsResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }
}
