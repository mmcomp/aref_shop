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
        if ($this->perPage === 'all') {
            return $out;
        }
        return new CollectionPaginator($out->forPage($this->pageNumber,$this->perPage), count($out),$this->perPage, $this->pageNumber);
    }
}
