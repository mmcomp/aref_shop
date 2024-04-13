<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReadingStationUsers2Collection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $collection =  $this->collection;
        // $today = Carbon::now()->toDateString();

        foreach ($collection as $indx => $row) {
            $weeklyPrograms = $row->weeklyPrograms;
            if (isset($weeklyPrograms[0]) && Carbon::parse($weeklyPrograms[0]->start)->gt(Carbon::now())) {
                $collection[$indx]->weeklyPrograms = [null, $weeklyPrograms[0]];
            }
        }

        return $collection;
    }
}
