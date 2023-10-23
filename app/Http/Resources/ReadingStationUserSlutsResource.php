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
                $sluts = null;
                foreach ($weeklyPrograms as $weeklyProgram) {
                    if (Carbon::now()->startOfWeek(Carbon::SATURDAY)->diffInDays(Carbon::parse($weeklyProgram->start)) === 0) {
                        $sluts = $weeklyProgram->sluts->map(function ($_slut) use ($slut) {
                            $_slut->isToday = $_slut->reading_station_slut_id !== $slut->id;
                            return $_slut;
                        });
                        break;
                    }
                }
                $userInformations[] = [
                    "user" => new UserResource($readingStationUser->user),
                    "sluts" => new ReadingStationUserSlutCollection($sluts),
                ];
            }
            return $userInformations;
        }
    }
}
