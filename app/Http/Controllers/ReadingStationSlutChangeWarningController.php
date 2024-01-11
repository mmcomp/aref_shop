<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationIndexSlutChangeWarningRequest;
use App\Http\Resources\ReadingStationSlutChangeWarningCollection;
use App\Models\ReadingStation;
use App\Models\ReadingStationSlutChangeWarning;
use Illuminate\Support\Facades\Auth;

class ReadingStationSlutChangeWarningController extends Controller
{
    public function index(ReadingStationIndexSlutChangeWarningRequest $request, ReadingStation $readingStation)
    {
        $sort = $request->exists('sort') ? $request->sort : "id";
        $sortDir = $request->exists('sort_dir') ? $request->sort_dir : "desc";
        $slutChangeWarnings = ReadingStationSlutChangeWarning::whereHas('readingStationSlutUser', function ($q1) use ($readingStation) {
            $q1->whereHas('slut', function ($q2) use ($readingStation) {
                $q2->where('reading_station_id', $readingStation->id);
            });
        });
        if ($request->exists('is_read')) {
            $slutChangeWarnings->where('is_read', $request->is_read === 'true');
        }
        if ($request->exists('reading_station_slut_user_id')) {
            $slutChangeWarnings->where('reading_station_slut_user_id', $request->reading_station_slut_user_id);
        }

        $slutChangeWarnings->orderBy($sort, $sortDir);
        $perPage = $request->exists('per_page') ? $request->per_page : env('PAGE_COUNT', 10);
        if ($perPage === 'all') {
            $slutChangeWarnings = $slutChangeWarnings->get();
        } else {
            $slutChangeWarnings = $slutChangeWarnings->paginate($perPage);
        }
        if ($request->exists('mark_as_read') && $request->mark_as_read === 'true') {
            $ids = $slutChangeWarnings->pluck('id');
            ReadingStationSlutChangeWarning::whereIn('id', $ids)->update(['is_read' => true, 'reader_id' => Auth::user()->id]);
        }
        return (new ReadingStationSlutChangeWarningCollection($slutChangeWarnings))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
