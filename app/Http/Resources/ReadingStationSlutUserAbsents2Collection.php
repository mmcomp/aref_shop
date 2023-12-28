<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use stdClass;

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
            $data = $items[0];
            $data->count = count($items);
            $data->score = 0;
            $data->details = [];
            foreach ($items as $item) {
                $reason = new stdClass;
                $reason->reason = $item->absenseReason ? $item->absenseReason->name : null;
                $reason->score = 2;
                switch ($item->absense_approved_status) {
                    case 'semi_approved':
                        $reason->score = 1;
                        break;

                    case 'approved':
                        $reason->score = 0;
                        break;
                }
                $reason->status = $item->absense_approved_status;
                $data->score += $reason->score;
                $data->details[] = $reason;
            }
            $out[] = $data;
        }
        return $out;
    }
}
