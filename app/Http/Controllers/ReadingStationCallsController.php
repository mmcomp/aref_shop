<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReadingStationResource;
use App\Models\ReadingStation;
use App\Models\User;
use App\Http\Controllers\Utils\ReadingStationAuth;
use App\Http\Requests\ReadingStationNoneExitCallCreateRequest;
use App\Http\Resources\ReadingStationAllCallsResource;
use App\Models\ReadingStationCall;
use App\Models\ReadingStationSlut;
use App\Models\ReadingStationSlutUser;
use App\Models\ReadingStationWeeklyProgram;
use Carbon\Carbon;

class ReadingStationCallsController extends Controller
{

    function index(ReadingStation $readingStation)
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

    function sendNoneExitCall(ReadingStationNoneExitCallCreateRequest $request, ReadingStation $readingStation, User $user, ReadingStationSlut $slut)
    {
        if (!$this->checkUserWithReadingStationAuth($readingStation, $user)) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station_call' => ['Reading station call are not available for you!']],
            ])->response()->setStatusCode(400);
        }


        if (!$this->hasProgram($user)) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station user does not have a plan for this week!']],
            ])->response()->setStatusCode(400);
        }

        $weeklyProgram = $this->thisWeekProgram($user);
        $slutUser = $weeklyProgram->sluts->where('reading_station_slut_id', $slut->id)->first();
        if (!$slutUser) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station_user' => ['Selected slut is not available for this user!']],
            ])->response()->setStatusCode(400);
        }

        $query = [];
        $now = Carbon::now()->toDateString();
        if ($slutUser->status === 'absent') {
            $query[] = [
                "reading_station_slut_user_id" => $slutUser->id,
                "reason" => "absence",
                "answered" => $request->answered,
                "description" => $request->description,
                "created_at" => $now,
                "updated_at" => $now,
            ];
        } else if (str_starts_with($slutUser->status, 'late_')) {
            $query[] = [
                "reading_station_slut_user_id" => $slutUser->id,
                "reason" => "latency",
                "answered" => $request->answered,
                "description" => $request->description,
                "created_at" => $now,
                "updated_at" => $now,
            ];
        }
        if (!$slutUser->is_required) {
            $query[] = [
                "reading_station_slut_user_id" => $slutUser->id,
                "reason" => "entry",
                "answered" => $request->answered,
                "description" => $request->description,
                "created_at" => $now,
                "updated_at" => $now,
            ];
        }
        ReadingStationCall::insert($query);

        return (new ReadingStationAllCallsResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    private function checkUserWithReadingStationAuth(ReadingStation $readingStation, ?User $user = null): bool
    {
        return ReadingStationAuth::checkUserWithReadingStationAuth($readingStation, $user);
    }


    private function hasProgram(User $user): bool
    {
        $date = Carbon::now();
        if ($user->readingStationUser && $user->readingStationUser->weeklyPrograms && count($user->readingStationUser->weeklyPrograms)) {
            $weeklyProgram = $user->readingStationUser->weeklyPrograms->where('end', $date->endOfWeek(Carbon::FRIDAY)->toDateString())->first();
            if ($weeklyProgram) {
                return true;
            }
        }

        return false;
    }

    private function thisWeekProgram(User $user): ReadingStationWeeklyProgram
    {
        $date = Carbon::now();
        return $user->readingStationUser->weeklyPrograms->where('end', $date->endOfWeek(Carbon::FRIDAY)->toDateString())->first();
    }
}
