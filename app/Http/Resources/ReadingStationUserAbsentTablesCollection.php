<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ReadingStationUserAbsentTablesCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $result = collect([]);
        foreach($this->collection as $cell) {
            if (!$result->where('reading_station_weekly_program_id', $cell->reading_station_weekly_program_id)->first()) {
                $result[] = $cell;
            }
        }

        return $result;
    }
}
