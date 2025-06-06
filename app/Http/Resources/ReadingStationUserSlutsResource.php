<?php

namespace App\Http\Resources;

use App\Models\ReadingStationUser;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationUserSlutsResource extends JsonResource
{
    private $slut;
    private $users = [];
    private $warnings = [];
    private $now;
    function __construct($resource, $slut, $warnings = [], $now = null)
    {
        $this->slut = $slut;
        $this->users = $resource;
        $this->warnings = $warnings;
        $this->now = $now === null ? Carbon::now() : $now;
        parent::__construct($resource[0]);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (count($this->users) > 0) {
            $slut = $this->slut;
            $userInformations = [];
            $readingStationUsers = $this->users;
            foreach ($readingStationUsers as $readingStationUser) {
                $weeklyPrograms = $readingStationUser->allWeeklyPrograms;
                $selectedSlut = null;
                $slutNames = [];
                $hasProgram = false;
                if (!$weeklyPrograms) continue;
                foreach ($weeklyPrograms as $weeklyProgram) {
                    if ($this->now->copy()->endOfWeek(Carbon::FRIDAY)->diffInDays(Carbon::parse($weeklyProgram->end)) === 0) {
                        if (count($weeklyProgram->sluts) > 0) {
                            $hasProgram = true;
                        }
                        $slutNames = $weeklyProgram->sluts->sort(function ($a, $b) {
                            if ($a->slut->start === $b->slut->start) return 0;
                            if (Carbon::parse($a->slut->start)->greaterThan(Carbon::parse($b->slut->start))) return 1;
                            return -1;
                        })->filter(function ($_slut) {
                            return $this->now->copy()->toDateString() == $_slut->day && $_slut->is_required;
                        })->map(function ($_slut) {
                            return $_slut->slut->name;
                        })->toArray();
                        $selectedSlut = $weeklyProgram->sluts
                            ->where('day', $this->now->copy()->toDateString())
                            ->where('reading_station_slut_id', $slut->id)->first();
                        $slutNames = array_unique($slutNames);
                        break;
                    }
                }

                $latestOperator =  $selectedSlut && $selectedSlut->user ? [
                    "first_name" =>  $selectedSlut->user->first_name,
                    "last_name" => $selectedSlut->user->last_name,
                    "updated_at" => $selectedSlut->updated_at,
                ] : null;
                $currentAbsentPresent = $readingStationUser->user->absentPresents->where('is_processed', 0)->first();
                if ($currentAbsentPresent) {
                    $absentPresentOperator = $currentAbsentPresent->operator ? [
                        "first_name" =>  $currentAbsentPresent->operator->first_name,
                        "last_name" => $currentAbsentPresent->operator->last_name,
                        "updated_at" => $currentAbsentPresent->updated_at,
                    ] : null;
                    if (
                        !$latestOperator ||
                        ($latestOperator &&
                            $absentPresentOperator &&
                            Carbon::parse($absentPresentOperator['updated_at'])->greaterThan(Carbon::parse($latestOperator['updated_at'])))
                    ) {
                        $latestOperator = $absentPresentOperator;
                    }
                }
                $absentPresents =  $readingStationUser->user->absentPresents($this->now)->get();
                $userInformations[] = [
                    "user" => new UserSmallResource($readingStationUser->user),
                    "table_number" => $readingStationUser->table_number,
                    "slut" => new ReadingStationUserSlutResource($selectedSlut),
                    "day" => $selectedSlut !== null ? $selectedSlut->day : null,
                    "operator" => $selectedSlut && $selectedSlut->user ? [
                        "first_name" =>  $selectedSlut->user->first_name,
                        "last_name" => $selectedSlut->user->last_name,
                        "updated_at" => $selectedSlut->updated_at,
                    ] : null,
                    "slutNames" => $slutNames,
                    "hasProgram" => $hasProgram,
                    "absentPresents" => new ReadingStationAbsentPresentCollection($absentPresents),
                    "latestOperator" => $latestOperator,
                ];
            }
            return ["data" => $userInformations, "warning" => $this->warnings];
        }
    }
}
