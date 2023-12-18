<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReadingStationUserAbsentsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $result = [];
        foreach($this->collection as $indx => $cell) {
            if ($indx === 0) continue;

            if ($this->near($this->collection[$indx - 1], ($this->collection[$indx]))) {
                $result[] = [$this->collection[$indx - 1], $this->collection[$indx]];
            } else {
                $result[] = $this->collection[$indx - 1];
                $result[] = $this->collection[$indx];
            }
        }
        return $result;
    }

    private function near($a, $b, $maxDiffMinutes = 15): bool {
        return(Carbon::parse($b->slut->start)->diffInMinutes(Carbon::parse($a->slut->end)) <= $maxDiffMinutes);
    }
}
