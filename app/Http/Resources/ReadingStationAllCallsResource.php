<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationAllCallsResource extends JsonResource
{
    private $exitCalls;

    public function __construct($resource, $exitCalls)
    {
        parent::__construct($resource);
        $this->exitCalls = $exitCalls;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->resource != null) {
            $all = count($this->resource);
            $optional_enters = 0;
            $delays = 0;
            $absents = 0;
            $exits = 0;
            $data = [];

            foreach ($this->resource as $userSlut) {
                $user = $userSlut->weeklyProgram->readingStationUser->user;
                $lastAbsentPresent = $user->absentPresents->where('is_processed', 1)->sortByDesc('updated_at')->first();
                $userStation = $userSlut->weeklyProgram->readingStationUser;
                $absentPresent = $userSlut->absentPresent;
                $absent = null;
                $last_call_status = null;
                $calls = $userSlut->absentPresent->calls;
                $allCalls = $calls
                    ->where('updated_at', '>=', Carbon::now()->toDateString() . ' 00:00:00')
                    ->where('updated_at', '<=', Carbon::now()->toDateString() . ' 23:59:59');
                $noneExitCalls = $calls->where('reason', '!=', 'exit')
                    ->where('updated_at', '>=', Carbon::now()->toDateString() . ' 00:00:00')
                    ->where('updated_at', '<=', Carbon::now()->toDateString() . ' 23:59:59');
                if ($lastAbsentPresent) {
                    $noneExitCalls = $noneExitCalls->where('updated_at', '>', $lastAbsentPresent->updated_at);
                    $allCalls = $allCalls->where('updated_at', '>', $lastAbsentPresent->updated_at);
                }
                if (count($noneExitCalls) > 0) {
                    $last_call_status = $noneExitCalls->sortByDesc('id')->first()->answered ? true : false;
                }
                $optional_enter = $userSlut->is_required ? false : true;
                if ($optional_enter) {
                    $optional_enters++;
                }
                $delay = null;
                $exit = null;
                if ($absentPresent) {
                    $hasCall = null;
                    $call = null;
                    $this->exitCalls->map(function ($_call) use (&$call, $user, $lastAbsentPresent) {
                        if ($_call->slutUser->weeklyProgram->readingStationUser->user->id === $user->id) {
                            if ($lastAbsentPresent) {
                                if (Carbon::parse($lastAbsentPresent->updated_at)->greaterThan(Carbon::parse($_call->updated_at))) {
                                    return;
                                }
                            }
                            $call = $_call;
                        }
                    });

                    if ($call) {
                        $hasCall = [
                            "answered" => $call->answered ? true : false,
                        ];
                    }
                    if (
                        $hasCall ||
                        $absentPresent->possible_exit_way ||
                        $absentPresent->reading_station_slut_user_exit_id ||
                        $absentPresent->exit_way ||
                        $absentPresent->possible_end
                    ) {
                        $exit = [
                            "hasCall" => $hasCall,
                            "possible_exit_way" => $absentPresent->possible_exit_way,
                            "exit_slut_id" => $absentPresent->reading_station_slut_user_exit_id,
                            "exit_way" => $absentPresent->exit_way,
                            "possible_end" => $absentPresent->possible_end,
                        ];
                        $exits++;
                    }
                }
                switch ($userSlut->status) {
                    case 'absent':
                        $absent = [
                            "reason_id" => $userSlut->reading_station_absent_reason_id,
                        ];
                        $absents++;
                        break;
                    case 'late_15':
                    case 'late_30':
                    case 'late_45':
                    case 'late_60':
                    case 'late_60_plus':
                        $delay = $userSlut->status;
                        $delays++;
                        break;
                }

                $data[] = [
                    "slut" => [
                        "id" => $userSlut->slut->id,
                        "name" => $userSlut->slut->name,
                    ],
                    "user" => [
                        "id" => $user->id,
                        "first_name" => $user->first_name,
                        "last_name" => $user->last_name,
                        "email" => $user->email,
                        "home_tell" => $user->home_tell,
                        "mother_cell" => $user->mother_cell,
                        "father_cell" => $user->father_cell,
                    ],
                    "table_number" => $userStation->table_number,
                    "absent" => $absent,
                    "optional_enter" => $optional_enter,
                    "delay" => $delay,
                    "exit" => $exit,
                    "call_numbers" => count($allCalls),
                    "last_call_status" => $last_call_status,
                ];
            }
            return [
                "all" => $all,
                "optional_enters" => $optional_enters,
                "delays" => $delays,
                "absents" => $absents,
                "exits" => $exits,
                "data" => $data,
            ];
        }
    }
}
