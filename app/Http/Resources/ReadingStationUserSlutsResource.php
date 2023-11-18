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
                $slutNames = [];
                foreach ($weeklyPrograms as $weeklyProgram) {
                    if (Carbon::now()->startOfWeek(Carbon::SATURDAY)->diffInDays(Carbon::parse($weeklyProgram->start)) === 0) {
                        $slutNames = $weeklyProgram->sluts->filter(function ($_slut) {
                            return Carbon::now()->toDateString() == $_slut->day;
                        })->map(function ($_slut) {
                            return $_slut->slut->name;
                        });
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
                ];
            }
            return $userInformations;
        }
    }
}
