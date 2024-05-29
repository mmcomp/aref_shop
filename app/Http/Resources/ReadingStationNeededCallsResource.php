<?php

namespace App\Http\Resources;

use App\Models\ReadingStationSlutUser;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationNeededCallsResource extends JsonResource
{
    protected $type;
    public function __construct($resource, $type = "all")
    {
        parent::__construct($resource);
        $this->type = $type;
    }

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
        $type = $this->type;
        if ($this->resource != null) {
            $all = 0;
            $optional_enters = 0;
            $delays = 0;
            $absents = 0;
            $exits = 0;
            $data = [];
            $exitUsers = [];
            $resource = $this->resource->sort(function ($a, $b) {
                if ($a->absentPresent && $b->absentPresent && $a->absentPresent->slutUserExit && $b->absentPresent->slutUserExit) {
                    return $a->absentPresent->slutUserExit->start > $b->absentPresent->slutUserExit->start ? 1 : -1;
                }
                return 0;
            });

            $hasCallData = [];
            $typeIsHasCalls = $type === 'has_calls';
            if ($typeIsHasCalls) {
                $type = 'all';
            }
            foreach ($resource as $userSlut) {
                $exitCallSituation = true;
                $noneExitCallSituation = true;
                $user = $userSlut->weeklyProgram->readingStationUser->user;
                $lastAbsentPresent = $user->absentPresents->where('is_processed', 1)->sortByDesc('updated_at')->first();
                $userStation = $userSlut->weeklyProgram->readingStationUser;
                $absentPresent = $userSlut->absentPresent;
                $absent = null;
                $last_call_status = null;
                if (!$absentPresent) continue;
                if ($filter && $lastAbsentPresent && Carbon::parse($lastAbsentPresent->updated_at)->greaterThanOrEqualTo(Carbon::parse($userSlut->updated_at))) continue;
                $calls = $absentPresent->calls;
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
                            $noneExitCallSituation = false;
                        }
                        break;
                    case 'late_15':
                    case 'late_30':
                    case 'late_45':
                    case 'late_60':
                    case 'late_60_plus':
                        $delay = $userSlut->status;
                        if (!$last_call_status) {
                            $delays++;
                            $noneExitCallSituation = false;
                        }
                        break;
                }
                if ($last_call_status === true) {
                    $noneExitCallSituation = true;
                }
                if (count($allCalls) > 0) {
                    $hasCallData[] = [
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
                if ($exitCallSituation && $noneExitCallSituation && $filter) continue;
                $all++;
                if ($type !== "all") {
                    if ($type === "absent" && $absent === null) continue;
                    if ($type === "absent" && $last_call_status) continue;
                    if ($type === "delay" && $delay === null) continue;
                    if ($type === "delay" && $last_call_status) continue;
                    if ($type === "exit" && $exit === null) continue;
                    if ($type === "optional_enter" && $optional_enter !== true) continue;
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
                if ($last_call_status) {
                    $hasCallData[] = [
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
            }
            if ($typeIsHasCalls) {
                $data = $hasCallData;
            }
            return [
                "all" => $all,
                "optional_enters" => $optional_enters,
                "delays" => $delays,
                "absents" => $absents,
                "exits" => $exits,
                "hasCallCount" => count($hasCallData),
                "data" => $data,
                // "hasCallData" => $hasCallData,
            ];
        }
    }
}
