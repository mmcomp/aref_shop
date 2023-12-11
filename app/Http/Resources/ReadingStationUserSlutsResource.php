<?php

namespace App\Http\Resources;

use App\Models\ReadingStationUser;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationUserSlutsResource extends JsonResource
{
    private $slut;
    private $users = [];
    function __construct($resource, $slut)
    {
        $this->slut = $slut;
        $this->users = $resource;
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
                $weeklyPrograms = $readingStationUser->weeklyPrograms;
                $selectedSlut = null;
                // $absentPresent = null;
                $slutNames = [];
                $hasProgram = false;
                foreach ($weeklyPrograms as $weeklyProgram) {
                    if (Carbon::now()->endOfWeek(Carbon::FRIDAY)->diffInDays(Carbon::parse($weeklyProgram->end)) === 0) {
                        if (count($weeklyProgram->sluts) > 0) {
                            $hasProgram = true;
                        }
                        $slutNames = $weeklyProgram->sluts->sort(function ($a, $b) {
                            if ($a->slut->start === $b->slut->start) return 0;
                            if (Carbon::parse($a->slut->start)->greaterThan(Carbon::parse($b->slut->start))) return 1;
                            return -1;
                        })->filter(function ($_slut) {
                            return Carbon::now()->toDateString() == $_slut->day && $_slut->is_required;
                        })->map(function ($_slut) {
                            return $_slut->slut->name;
                        })->toArray();
                        $selectedSlut = $weeklyProgram->sluts
                                            ->where('day', Carbon::now()->toDateString())
                                            ->where('reading_station_slut_id', $slut->id)->first();
                        break;
                    }
                }
                $userInformations[] = [
                    "user" => new UserResource($readingStationUser->user),
                    "slut" => new ReadingStationUserSlutResource($selectedSlut),
                    "slutNames" => $slutNames,
                    "hasProgram" => $hasProgram,
                    "absentPresents" => new ReadingStationAbsentPresentCollection($readingStationUser->user->absentPresents),
                ];
            }
            return $userInformations;
        }
    }
}
