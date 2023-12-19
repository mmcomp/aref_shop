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
        $groupId = 0;
        foreach ($this->collection as $indx => $cell) {
            if ($indx === 0) continue;

            $a = $this->collection[$indx - 1];
            $b = $cell;
            if ($this->near($a, $b)) {
                if ($a->groupId) {
                    $this->collection[$indx]->groupId = $a->groupId;
                } else {
                    $this->collection[$indx - 1]->groupId = $groupId;
                    $this->collection[$indx]->groupId = $groupId;
                    $groupId++;
                }
            } else {
                if ($a->groupId) {
                    $this->collection[$indx]->groupId = $groupId;
                    $groupId++;
                } else {
                    $this->collection[$indx - 1]->groupId = $groupId;
                    $groupId++;
                    $this->collection[$indx]->groupId = $groupId;
                    $groupId++;
                }
            }
        }
        return $this->collection->groupBy('groupId');
    }

    private function near($a, $b, $maxDiffMinutes = 46): bool
    {
        return (Carbon::parse($b->slut->start)->diffInMinutes(Carbon::parse($a->slut->end)) <= $maxDiffMinutes);
    }
}
