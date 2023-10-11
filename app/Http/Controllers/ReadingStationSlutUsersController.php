<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingStationSlutUsersCreateRequest;
use App\Http\Resources\ReadingStationSlutUsersResource;
use App\Models\ReadingStationSlutUser;
use App\Models\ReadingStationWeeklyProgram;
use App\Models\User;
use Carbon\Carbon;

class ReadingStationSlutUsersController extends Controller
{
    private function getWeeklyProgram(User $user, $week): ReadingStationWeeklyProgram
    {
        $package = $user->readingStationUser->package;
        $weeklyPrograms = $user->readingStationUser->weeklyPrograms;
        $weeklyProgram = null;
        $nextWeekStartDate = Carbon::now()->endOfWeek(Carbon::FRIDAY)->addDay();
        $nextWeekStart = $nextWeekStartDate->toDateString();
        $nextWeekEnd = $nextWeekStartDate->clone()->endOfWeek(Carbon::FRIDAY)->toDateString();
        foreach ($weeklyPrograms as $weekProgram) {
            if ($week === 'current' && Carbon::now()->between(Carbon::parse($weekProgram->start), Carbon::parse($weekProgram->end), true)) {
                $weeklyProgram = $weekProgram;
                break;
            } else if ($week === 'next' && $nextWeekStart === $weekProgram->start && $nextWeekEnd === $weekProgram->end) {
                $weeklyProgram = $weekProgram;
                break;
            }
        }
        if (!$weeklyProgram) {
            $weeklyProgram = new ReadingStationWeeklyProgram();
            $weeklyProgram->reading_station_user_id = $user->readingStationUser->id;
            $weeklyProgram->name = $package->name;
            $weeklyProgram->required_time = $package->required_time;
            $weeklyProgram->optional_time = $package->optional_time;
            if ($week === 'current') {
                $weeklyProgram->start = Carbon::now()->startOfWeek(Carbon::SATURDAY)->toDateString();
                $weeklyProgram->end = Carbon::now()->endOfWeek(Carbon::FRIDAY)->toDateString();
            } else {
                $weeklyProgram->start = $nextWeekStart;
                $weeklyProgram->end = $nextWeekEnd;
            }
            $weeklyProgram->save();
        }

        return $weeklyProgram;
    }

    public function store(ReadingStationSlutUsersCreateRequest $request, User $user)
    {
        if (!$user->is_reading_station_user) {
            return (new ReadingStationSlutUsersResource(null))->additional([
                'errors' => ['reading_station_slut_user' => ['User does not have blong to any Reading Station!']],
            ])->response()->setStatusCode(400);
        }
        $sluts = $user->readingStationUser->readingStation->sluts;
        $weeklyProgram = $this->getWeeklyProgram($user, $request->week);
        if (ReadingStationSlutUser::where("reading_station_weekly_program_id", $weeklyProgram->id)->first()) {
            return (new ReadingStationSlutUsersResource(null))->additional([
                'errors' => ['reading_station_slut_user' => ['User has a program for the requested week!']],
            ])->response()->setStatusCode(400);
        }
        foreach ($request->data as $data) {
            if (!Carbon::parse($data['day'])->between(Carbon::parse($weeklyProgram->start), Carbon::parse($weeklyProgram->end), true)) {
                return (new ReadingStationSlutUsersResource(null))->additional([
                    'errors' => ['reading_station_slut_user' => ['The selected day is not in the week!']],
                ])->response()->setStatusCode(400);
            }
        }
        $requestSluts = [];
        foreach ($request->data as $data) {
            $requestSluts[] = $data['reading_station_slut_id'];
        }
        $availableSluts = [];
        foreach ($sluts as $slut) {
            $availableSluts[] = $slut->id;
        }
        if (count(array_intersect($requestSluts, $availableSluts)) !== count($requestSluts)) {
            $intersect = array_intersect($requestSluts, $availableSluts);
            $diff = array_diff($requestSluts, $intersect);
            return (new ReadingStationSlutUsersResource(null))->additional([
                'errors' => ['reading_station_slut_user' => ['Some of sluts are not correct!', $diff]],
            ])->response()->setStatusCode(400);
        }
        $query = [];
        foreach ($request->data as $data) {
            $query[] = [
                "reading_station_weekly_program_id" => $weeklyProgram->id,
                "reading_station_slut_id" => $data['reading_station_slut_id'],
                "day" => $data['day'],
                "is_required" => true,
                "status" => "defined",
                "created_at" => Carbon::now()->toDateString(),
                "updated_at" => Carbon::now()->toDateString(),
            ];
        }
        ReadingStationSlutUser::insert($query);
        return (new ReadingStationSlutUsersResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }
}
