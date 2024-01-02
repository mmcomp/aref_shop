<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationSlutUserPackageWeeklyProgramResource extends JsonResource
{
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
                'id' => $this->id,
                'start' => $this->start,
                'end' => $this->end,
                'package_name' => $this->readingStationUser->package->name,
                'grade_package_name' => $this->readingStationUser->user->gradePackage ? $this->readingStationUser->user->gradePackage->name : null,
                'point' => $this->package_point,
            ];
        }
    }
}
