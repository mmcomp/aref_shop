<?php

namespace App\Http\Resources;

use App\Utils\CollectionPaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;
use stdClass;

class ReadingStationSlutUserLatesCollection extends ResourceCollection
{
    private $perPage;
    private $pageNumber;
    function __construct($resource, $perPage = null, $pageNumber = null)
    {
        $this->perPage = $perPage;
        $this->pageNumber = $pageNumber;
        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $total = 0;
        $out = collect([]);
        $lateToMinute = [
            'late_15' =>15,
            'late_30' =>30,
            'late_45' =>45,
            'late_60' =>60,
            'late_60_plus' =>90,
        ];
        $groupByResult = $this->collection->groupBy('day');
        foreach ($groupByResult as $day => $items) {
            $data = $items[0];
            $data->count = count($items);
            $data->point = 0;
            $data->minutes = 0;
            $data->details = [];
            foreach ($items as $item) {
                $reason = new stdClass;
                $reason->status = $item->status;
                $reason->point = -1;
                if ($item->status === 'late_60_plus') {
                    $reason->point = -2;
                }
                $data->point += $reason->point;
                $data->details[] = $reason;
                $data->minutes += $lateToMinute[$item->status];
            }
            $out[] = $data;
        }
        $total = $out->sum('point');
        if ($this->perPage === 'all') {
            return ['data' => $out, 'total_value' => $total];
        }
        return new CollectionPaginator($out->forPage($this->pageNumber,$this->perPage), count($out),$this->perPage, $total, $this->pageNumber);
    }
}
