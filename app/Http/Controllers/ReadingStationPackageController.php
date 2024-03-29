<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationPackageCreateRequest;
use App\Http\Requests\ReadingStationPackagesIndexRequest;
use App\Http\Requests\ReadingStationPackageUpdateRequest;
use App\Http\Resources\ReadingStationPackagesCollection;
use App\Http\Resources\ReadingStationPackagesResource;
use App\Models\ReadingStationPackage;

class ReadingStationPackageController extends Controller
{
    function store(ReadingStationPackageCreateRequest $request)
    {
        $found = ReadingStationPackage::where("name", $request->name)->first();
        if ($found) {
            return (new ReadingStationPackagesResource(null))->additional([
                'errors' => ['reading_station_package' => ['Reading station package with this name exists!']],
            ])->response()->setStatusCode(400);
        }
        ReadingStationPackage::create([
            "name" => $request->name, 
            "required_time" => $request->required_time, 
            "optional_time" => $request->optional_time,
            "grade" => $request->grade,
            "step" => $request->step,
        ]);
        return (new ReadingStationPackagesResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    function update(ReadingStationPackageUpdateRequest $request)
    {
        $readingStationPackage = ReadingStationPackage::find($request->id);
        if (!$readingStationPackage) {
            return (new ReadingStationPackagesResource(null))->additional([
                'errors' => ['reading_station' => ['Reading station not found!']],
            ])->response()->setStatusCode(404);
        }

        $readingStationPackage->name = $request->exists('name') ? $request->name : $readingStationPackage->name;
        $readingStationPackage->required_time = $request->exists('required_time') ? $request->required_time : $readingStationPackage->required_time;
        $readingStationPackage->optional_time = $request->exists('optional_time') ? $request->optional_time : $readingStationPackage->optional_time;
        $readingStationPackage->grade = $request->exists('grade') ? $request->grade : $readingStationPackage->grade;
        $readingStationPackage->step = $request->exists('step') ? $request->step : $readingStationPackage->step;

        $readingStationPackage->save();
        return (new ReadingStationPackagesResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    public function destroy(ReadingStationPackage $readingStationPackage)
    {
        $readingStationPackage->delete();
        return (new ReadingStationPackagesResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }

    function index(ReadingStationPackagesIndexRequest $request) {
        $sort = "id";
        $sortDir = "desc";
        $paginatedReadingStations = [];
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sortDir = $request->get('sort_dir');
        }
        if ($request->get('per_page') == "all") {
            $paginatedReadingStations = ReadingStationPackage::orderBy($sort, $sortDir)->get();
        } else {
            $perPage = $request->get('per_page');
            if (!$perPage) {
                $perPage = env('PAGE_COUNT');
            }
            $paginatedReadingStations = ReadingStationPackage::orderBy($sort, $sortDir)->paginate($perPage);
        }
        return (new ReadingStationPackagesCollection($paginatedReadingStations))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function findOne(ReadingStationPackage $readingStationPackage)
    {
        return (new ReadingStationPackagesResource($readingStationPackage))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
