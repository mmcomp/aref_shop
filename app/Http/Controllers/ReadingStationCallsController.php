<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReadingStationResource;
use App\Models\ReadingStation;
use App\Models\User;
use App\Http\Controllers\Utils\ReadingStationAuth;
use App\Http\Requests\ReadingStationExitSlutUpdateRequest;
use App\Http\Requests\ReadingStationNoneExitCallCreateRequest;
use App\Http\Resources\ReadingStationAllCallsResource;
use App\Models\ReadingStationAbsentPresent;
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

    function updateExitSlutId(ReadingStationExitSlutUpdateRequest $request, ReadingStation $readingStation, User $user, ReadingStationSlut $slut)
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

        if ($request->exists('reading_station_absent_reason_id')) {
            $weeklyProgram = $this->thisWeekProgram($user);
            $slutUser = $weeklyProgram->sluts->where('reading_station_slut_id', $slut->id)->first();
            if ($slutUser->status !== 'absent') {
                return (new ReadingStationResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station user were not absent then!']],
                ])->response()->setStatusCode(400);
            }
            $slutUser->reading_station_absent_reason_id = $request->reading_station_absent_reason_id;
            $slutUser->save();

            return (new ReadingStationResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(201);
        }

        $today = Carbon::now()->toDateString();
        $absentPresent = ReadingStationAbsentPresent::where('user_id', $user->id)
            ->where('reading_station_id', $readingStation->id)
            ->where('day', $today)
            ->where('is_processed', 0)
            ->first();
        if (!$absentPresent) {
            $absentPresent = new ReadingStationAbsentPresent();
            $absentPresent->user_id = $user->id;
            $absentPresent->reading_station_id = $readingStation->id;
            $absentPresent->day = $today;
        }
        $absentPresent->reading_station_slut_user_exit_id = $request->exists('reading_station_slut_user_exit_id') ? $request->reading_station_slut_user_exit_id : $absentPresent->reading_station_slut_user_exit_id;
        $absentPresent->exit_way = $request->exists('exit_way') ? $request->exit_way : $absentPresent->exit_way;
        $absentPresent->save();

        return (new ReadingStationResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    function sendExitCall(ReadingStationNoneExitCallCreateRequest $request, ReadingStation $readingStation, User $user, ReadingStationSlut $slut)
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

        $today = Carbon::now()->toDateString();
        $absentPresent = ReadingStationAbsentPresent::where('user_id', $user->id)
            ->where('reading_station_id', $readingStation->id)
            ->where('day', $today)
            ->where('is_processed', 0)
            ->where('reading_station_slut_user_exit_id', '!=', 'NULL')
            ->first();
        if (!$absentPresent) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station user does not have a exit slut defined!']],
            ])->response()->setStatusCode(400);
        }

        $exitSlutId = $absentPresent->reading_station_slut_user_exit_id;

        $weeklyProgram = $this->thisWeekProgram($user);
        $slutUser = $weeklyProgram->sluts->where('reading_station_slut_id', $exitSlutId)->first();
        if (!$slutUser) {
            $slutUser = new ReadingStationSlutUser();
            $slutUser->reading_station_slut_id = $exitSlutId;
            $slutUser->reading_station_weekly_program_id = $weeklyProgram->id;
            $slutUser->day = $today;
            $slutUser->is_required = 0;
            $slutUser->save();
        }
        if (ReadingStationCall::where('reading_station_slut_user_id', $slutUser->id)->where('reason', 'exit')->first()) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station_user' => ['Reading station user already has an exit call for today for this slut!']],
            ])->response()->setStatusCode(400);
        }

        $exitCall = new ReadingStationCall();
        $exitCall->reading_station_slut_user_id = $slutUser->id;
        $exitCall->reason = 'exit';
        $exitCall->answered = $request->answered;
        $exitCall->save();

        return (new ReadingStationResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
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
