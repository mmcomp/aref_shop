<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationCreateRequest;
use App\Http\Requests\ReadingStationIndexRequest;
use App\Http\Requests\ReadingStationUpdateRequest;
use App\Http\Resources\ReadingStation2Collection;
use App\Http\Resources\ReadingStationResource;
use App\Models\ReadingStation;
use App\Models\ReadingStationUser;
use App\Models\User;
use App\Utils\ReadingStationSms;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Utils\ReadingStationAuth;
use App\Http\Resources\ReadingStationAllCallsResource;
use App\Models\ReadingStationSlutUser;
use Carbon\Carbon;

class ReadingStationCallsController extends Controller
{

    function index(ReadingStationIndexRequest $request, ReadingStation $readingStation)
    {
        if (!$this->checkUserWithReadingStationAuth($readingStation)) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station_call' => ['Reading station call are not available for you!']],
            ])->response()->setStatusCode(400);
        }
        $today = Carbon::now()->toDateString();
        $now = Carbon::now()->format("H:i:s");
        $weeklyPrograms = [];
        $availableSluts = $readingStation->sluts
                            ->where('start', '<=', $now)
                            ->map(function ($slut) {
                                return $slut->id;
                            })
                            ->toArray();
        $readingStation->users->map(function ($user) use (&$weeklyPrograms) {
            return $user->weeklyPrograms->map(function ($weeklyProgram) use (&$weeklyPrograms) {
                $weeklyPrograms[] = $weeklyProgram->id;
            });
        })->toArray();
        $todaySluts = ReadingStationSlutUser::whereIn('reading_station_weekly_program_id', $weeklyPrograms)
            ->where('day', $today)
            ->whereIn('reading_station_slut_id', $availableSluts)
            ->where('status', '!=', 'defined')
            ->get();

        return (new ReadingStationAllCallsResource($todaySluts))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    private function checkUserWithReadingStationAuth(ReadingStation $readingStation): bool
    {
        return ReadingStationAuth::checkUserWithReadingStationAuth($readingStation);
    }
}
