<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;

class ReadingStationSlutUserAbsents2Collection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $out = collect([]);
        $groupByResult = $this->collection->groupBy('day');
        foreach ($groupByResult as $day => $items) {
            $items[0]->count = count($items); 
            $data = $items[0];
            $data['details'] = $items;
            $out[] = $data;
        }
        return $out;
    }
}
