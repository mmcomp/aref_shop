<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReadingStationResource;
use App\Models\ReadingStation;
use App\Models\User;
use App\Http\Controllers\Utils\ReadingStationAuth;
use App\Http\Requests\ReadingStationCallCreateRequest;
use App\Http\Requests\ReadingStationCallIndexRequest;
use App\Http\Requests\ReadingStationExitSlutUpdateRequest;
use App\Http\Resources\ReadingStationAllCallsResource;
use App\Http\Resources\ReadingStationNeededCallsResource;
use App\Models\ReadingStationAbsentPresent;
use App\Models\ReadingStationAbsentReason;
use App\Models\ReadingStationCall;
use App\Models\ReadingStationSlut;
use App\Models\ReadingStationSlutUser;
use App\Models\ReadingStationWeeklyProgram;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReadingStationCallsController extends Controller
{

    function index(ReadingStationCallIndexRequest $request, ReadingStation $readingStation)
    {
        if (!$this->checkUserWithReadingStationAuth($readingStation)) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station_call' => ['Reading station call are not available for you!']],
            ])->response()->setStatusCode(400);
        }
        $today = Carbon::now()->toDateString();
        $now = Carbon::now()->format("H:i:s");
        if ($request->exists('date')) {
            $date = Carbon::parse($request->date);
            $today = $date->toDateString();
            $now = $date->format("H:i:s");
        }
        $weeklyPrograms = [];
        $availableSluts = ReadingStationSlut::select('id')->where('reading_station_id', $readingStation->id)
            ->where('start', '<=', $now)
            ->get()
            ->map(function ($slut) {
                return $slut->id;
            })
            ->toArray();

        // dd($availableSluts);
        $weeklyPrograms = ReadingStationWeeklyProgram::whereHas('readingStationUser', function ($query) use ($readingStation) {
            $query->where('reading_station_id', $readingStation->id);
        })->get()
            ->map(function ($slut) {
                return $slut->id;
            })
            ->toArray();

        // $availableSluts = $readingStation->sluts
        //     ->where('start', '<=', $now)
        //     ->map(function ($slut) {
        //         return $slut->id;
        //     })
        //     ->toArray();
        // $readingStation->users->map(function ($user) use (&$weeklyPrograms) {
        //     return $user->weeklyPrograms->map(function ($weeklyProgram) use (&$weeklyPrograms) {
        //         $weeklyPrograms[] = $weeklyProgram->id;
        //     });
        // })->toArray();
        // $absentPresents = ReadingStationAbsentPresent::whereHas('user', function ($q1) use ($weeklyPrograms) {
        //     $q1->whereHas('readingStationUser', function ($q2) use ($weeklyPrograms) {
        //         $q2->whereHas('allWeeklyPrograms', function ($q3) use ($weeklyPrograms) {
        //             $q3->whereIn('id', $weeklyPrograms);
        //         });
        //     });
        // })->with(['user', 'calls'])->get();
        // return $absentPresents;
        $todaySluts = ReadingStationSlutUser::whereIn('reading_station_weekly_program_id', $weeklyPrograms)
            ->with('weeklyProgram.readingStationUser.user')
            // ->with('absentPresent.calls')
            ->with('slut')
            ->where('day', $today)
            ->whereIn('reading_station_slut_id', $availableSluts)
            ->where('status', '!=', 'defined')
            ->get();
        // foreach($todaySluts as $indx => $todaySlut) {
        //     $absentPresent = $absentPresents->where('user_id', $todaySlut->weeklyProgram->readingStationUser->user->id)->first();
        //     $todaySluts[$indx]->absentPresent = $absentPresent;
        // }
        // return $todaySluts;
        return (new ReadingStationNeededCallsResource($todaySluts, $request->type))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    function sendCall(ReadingStationCallCreateRequest $request, ReadingStation $readingStation, User $user, ReadingStationSlut $slut)
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
        $today = Carbon::now()->toDateString();
        $slutUser = $weeklyProgram->sluts->where('reading_station_slut_id', $slut->id)->where('day', $today)->first();
        if (!$slutUser) {
            return (new ReadingStationResource(null))->additional([
                'errors' => ['reading_station_user' => ['Selected slut is not available for this user!']],
            ])->response()->setStatusCode(400);
        }

        $now = str_replace('T', ' ', Carbon::now()->toDateTimeLocalString());

        $absentPresent = $user->absentPresents->where('is_processed', 0)->first();

        ReadingStationCall::insert([[
            "reading_station_absent_present_id" => $absentPresent->id,
            "reading_station_slut_user_id" => $slutUser->id,
            "reason" => $request->reason,
            "description" => $request->description,
            "caller_user_id" => Auth::user()->id,
            "created_at" => $now,
            "updated_at" => $now,
        ]]);

        return (new ReadingStationAllCallsResource([$slutUser]))->additional([
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

        $today = Carbon::now()->toDateString();
        if ($request->exists('reading_station_absent_reason_id')) {
            $weeklyProgram = $this->thisWeekProgram($user);
            $slutUser = $weeklyProgram->sluts->where('reading_station_slut_id', $slut->id)->where('day', $today)->first();
            if ($slutUser->status !== 'absent') {
                return (new ReadingStationResource(null))->additional([
                    'errors' => ['reading_station_user' => ['Reading station user were not absent then!']],
                ])->response()->setStatusCode(400);
            }
            $absentReason = ReadingStationAbsentReason::find($request->reading_station_absent_reason_id);
            $slutUser->reading_station_absent_reason_id = $request->reading_station_absent_reason_id;
            $slutUser->reading_station_absent_reason_score = $absentReason->score;
            $slutUser->save();

            $weeklyProgram->strikes_done += $absentReason->score;
            $weeklyProgram->save();

            return (new ReadingStationResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(201);
        }

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
