<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use stdClass;

class ReadingStationSlutUserLatesCollection extends ResourceCollection
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
            $data = $items[0];
            $data->count = count($items);
            $data->score = 0;
            $data->details = [];
            foreach ($items as $item) {
                $reason = new stdClass;
                $reason->status = $item->status;
                $reason->score = 1;
                if ($item->status === 'late_60_plus') {
                    $reason->score = 2;
                }
                $data->score += $reason->score;
                $data->details[] = $reason;
            }
            $out[] = $data;
        }
        return $out;
    }
}
