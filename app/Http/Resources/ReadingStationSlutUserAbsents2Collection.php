<?php

namespace App\Http\Resources;

use App\Utils\CollectionPaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;
use stdClass;

class ReadingStationSlutUserAbsents2Collection extends ResourceCollection
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
        $out = collect([]);
        $groupByResult = $this->collection->groupBy('day');
        foreach ($groupByResult as $day => $items) {
            $data = $items[0];
            $data->count = count($items);
            $data->point = 0;
            $data->details = [];
            foreach ($items as $item) {
                $reason = new stdClass;
                $reason->reason = $item->absenseReason ? $item->absenseReason->name : null;
                $reason->point = -2;
                switch ($item->absense_approved_status) {
                    case 'semi_approved':
                        $reason->point = -1;
                        break;

                    case 'approved':
                        $reason->point = 0;
                        break;
                }
                $reason->status = $item->absense_approved_status;
                $data->point += $reason->point;
                $data->details[] = $reason;
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
