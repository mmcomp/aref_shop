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
            }
        }
        return $this->collection->groupBy('groupId');
        /*
        $result = [];
        foreach ($this->collection as $indx => $cell) {
            if ($indx === 0) {
                $result [] = $cell;
                continue;
            }

            $lastArray = $result[count($result) - 1];
            $lastCell = null;
            if (!is_array($lastArray)) {
                $lastArray = null;
                $lastCell = $result[count($result) - 1];
            } else {
                $lastCell = $lastArray[count($lastArray) - 1];
            }
            // dump($lastArray);
            // dump($lastCell);
            $a = $this->collection[$indx - 1];
            $b = $this->collection[$indx];
            if ($this->near($a, $b)) {
                if ($this->near($lastCell, $a)) {
                    if ($lastArray) {
                        $result[count($result) - 1][] = $a;
                        $result[count($result) - 1][] = $b;
                    } else {
                        array_pop($result);
                        $result[] = [$lastCell, $a, $b];
                    }
                } else {
                    $result[] = [$a, $b];
                }
            } else {
                if ($this->near($lastCell, $a)) {
                    if ($lastArray) {
                        $result[count($result) - 1][] = $a;
                    } else {
                        array_pop($result);
                        $result[] = [$lastCell, $a];
                    }
                } else {
                    $result[] = [$a, $b];
                }
            }
        }
        // dd();
        return $result;
        */
    }

    private function near($a, $b, $maxDiffMinutes = 15): bool
    {
        return (Carbon::parse($b->slut->start)->diffInMinutes(Carbon::parse($a->slut->end)) <= $maxDiffMinutes);
    }
}
