<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationStudentReportResource extends JsonResource
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
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'national_code' => $this->national_code,
                'table_number' => $this->readingStationUser->table_number,
                'school' => $this->school,
                'major' => $this->major,
                'grade' => $this->grade,
            ];
        }
    }
}
