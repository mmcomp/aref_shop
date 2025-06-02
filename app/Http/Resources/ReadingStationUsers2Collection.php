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

        foreach ($collection as $indx => $row) {
            $weeklyPrograms = $row->weeklyPrograms->sortBy('start');

            if (count($weeklyPrograms) === 1 && Carbon::parse($weeklyPrograms->first()->start)->gt(Carbon::now())) {
                $collection[$indx]->weeklyPrograms = [null, $weeklyPrograms->first()];
            }else {
                $collection[$indx]->weeklyPrograms = $collection[$indx]->weeklyPrograms->sortBy('start');
            }

        }
        return $collection;
    }
}
