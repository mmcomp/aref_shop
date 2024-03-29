<?php

namespace App\Http\Resources;

use App\Models\ReadingStationSlutUser;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationNeededCallsResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->processSlutUser(true);
    }

    public function processSlutUser(bool $filter = false)
    {
        if ($this->resource != null) {
            $all = 0;
            $optional_enters = 0;
            $delays = 0;
            $absents = 0;
            $exits = 0;
            $data = [];
            $exitUsers = [];
            $resource = $this->resource->sort(function ($a, $b) {
                if ($a->absentPresent && $b->absentPresent) {
                    return $a->absentPresent->slutUserExit->start > $b->absentPresent->slutUserExit->start ? 1 : -1;
                }
                return 1;
            });

            foreach ($resource as $userSlut) {
                $exitCallSituation = true;
                $noneExitCallSituation = true;
                $user = $userSlut->weeklyProgram->readingStationUser->user;
                $lastAbsentPresent = $user->absentPresents->where('is_processed', 1)->sortByDesc('updated_at')->first();
                $userStation = $userSlut->weeklyProgram->readingStationUser;
                $absentPresent = $userSlut->absentPresent;
                $absent = null;
                $last_call_status = null;
                if (!$userSlut->absentPresent) continue;
                if ($filter && $lastAbsentPresent && Carbon::parse($lastAbsentPresent->updated_at)->greaterThanOrEqualTo(Carbon::parse($userSlut->updated_at))) continue;
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
                    $last_call_status = $noneExitCalls->sortByDesc('id')->first()->reason !== 'no_answered';
                }
                $optional_enter = $userSlut->is_required ? false : true;
                if ($optional_enter && !$last_call_status) {
                    $optional_enters++;
                    $noneExitCallSituation = false;
                }
                $delay = null;
                $exit = null;
                if ($absentPresent && !in_array($user->id, $exitUsers)) {
                    $hasCall = false;
                    $call = $allCalls->whereIn('reason', ['exit', 'all'])->first();

                    if ($call) {
                        $hasCall = true;
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
                        if (!$hasCall) {
                            $exits++;
                            $exitUsers[] = $user->id;
                            $exitCallSituation = false;
                        }
                    }
                }
                switch ($userSlut->status) {
                    case 'absent':
                        $absent = [
                            "reason_id" => $userSlut->reading_station_absent_reason_id,
                        ];
                        if (!$last_call_status) {
                            $absents++;
                        }
                        $noneExitCallSituation = false;
                        break;
                    case 'late_15':
                    case 'late_30':
                    case 'late_45':
                    case 'late_60':
                    case 'late_60_plus':
                        $delay = $userSlut->status;
                        if (!$last_call_status) {
                            $delays++;
                        }
                        $noneExitCallSituation = false;
                        break;
                }
                if ($last_call_status === true) {
                    $noneExitCallSituation = true;
                }

                if ($exitCallSituation && $noneExitCallSituation && $filter) continue;
                $all++;
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
