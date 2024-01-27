<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationAbsentReasonsCreateRequest;
use App\Http\Requests\ReadingStationAbsentReasonsIndexRequest;
use App\Http\Requests\ReadingStationAbsentReasonsUpdateRequest;
use App\Http\Resources\ReadingStationAbsentReasonsCollection;
use App\Http\Resources\ReadingStationAbsentReasonsResource;
use App\Models\ReadingStationAbsentReason;
use Illuminate\Http\Request;

class ReadingStationAbsentReasonsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ReadingStationAbsentReasonsIndexRequest $request)
    {
        $sort = "id";
        $sortDir = "desc";
        $paginatedReadingStations = [];
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sortDir = $request->get('sort_dir');
        }
        if ($request->get('per_page') == "all") {
            $paginatedReadingStations = ReadingStationAbsentReason::orderBy($sort, $sortDir)->get();
        } else {
            $perPage = $request->get('per_page');
            if (!$perPage) {
                $perPage = env('PAGE_COUNT');
            }
            $paginatedReadingStations = ReadingStationAbsentReason::orderBy($sort, $sortDir)->paginate($perPage);
        }
        return (new ReadingStationAbsentReasonsCollection($paginatedReadingStations))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ReadingStationAbsentReasonsCreateRequest $request)
    {
        ReadingStationAbsentReason::create(["name"=>$request->name, "score"=>$request->score]);
        return (new ReadingStationAbsentReasonsResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ReadingStationAbsentReason $readingStationAbsentReason)
    {
        return (new ReadingStationAbsentReasonsResource($readingStationAbsentReason))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ReadingStationAbsentReasonsUpdateRequest $request)
    {
        $found = ReadingStationAbsentReason::find($request->id);
        if ($request->name && $found->name !== $request->name) {
            $nameFound = ReadingStationAbsentReason::where("name", $request->name)->first();
            if ($nameFound) {
                return (new ReadingStationAbsentReasonsResource(null))->additional([
                    'errors' => ['reading_station_absent_reason' => ['Reading station absent reason with the same name available!']],
                ])->response()->setStatusCode(400);
            }
            $found->name = $request->name;
        }
        if ($request->score) {
            $found->score = $request->score;
        }
        $found->save();

        return (new ReadingStationAbsentReasonsResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReadingStationAbsentReason $readingStationAbsentReason)
    {
        $readingStationAbsentReason->delete();
    
        return (new ReadingStationAbsentReasonsResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }
}
