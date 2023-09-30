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
        $sort_dir = "desc";
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sort_dir = $request->get('sort_dir');
        }
        if ($request->get('per_page') == "all") {
            $paginated_users = ReadingStation::orderBy($sort, $sort_dir)->get();

        } else {
            $paginated_users = ReadingStation::orderBy($sort, $sort_dir)->paginate(env('PAGE_COUNT'));
        }
        return (new ReadingStationCollection($paginated_users))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
