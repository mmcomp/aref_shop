<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationWeeklyPrograms4Resource extends JsonResource
{
    private $readingStationData;
    function __construct($resource, $readingStationData)
    {
        $this->readingStationData = $readingStationData;
        parent::__construct($resource);
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

            return [
                'userData' => [
                    'reading_minutes'=> $this->required_time_done + $this->optional_time_done,
                    'avarage_reading_minutes'=> intval(($this->required_time_done + $this->optional_time_done) / 7),
                    'absents' => [
                        "approved" => $this->approved_absent_day,
                        "semi_approved" => $this->semi_approved_absent_day,
                        "all" => $this->absent_day,
                    ],
                    'lates' => $this->late_day,
                ],
                'readingStationData' => $this->readingStationData,
            ];
        }
    }
}
