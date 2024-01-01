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
        if ($this->perPage === 'all') {
            return $out;
        }
        return new CollectionPaginator($out->forPage($this->pageNumber,$this->perPage), count($out),$this->perPage, $this->pageNumber);
    }
}
