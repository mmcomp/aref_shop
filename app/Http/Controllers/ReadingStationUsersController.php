<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationUsersIndexRequest;
use App\Http\Requests\UserIndexRequest;
use App\Http\Resources\ReadingStationUsers2Collection;
use App\Http\Resources\ReadingStationUsersCollection;
use App\Models\ReadingStation;
use App\Models\ReadingStationUser;
use Illuminate\Http\Request;

class ReadingStationUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(UserIndexRequest $request) {
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
        ])->response()->setStatusCode(200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
