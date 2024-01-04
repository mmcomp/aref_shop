<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ReadingStationAbsentReportCollection extends ResourceCollection
{
    private $count;
    private $approvedCount;
    private $semiApprovedCount;
    private $notApprovedCount;
    function __construct($resource, $count, $approvedCount, $semiApprovedCount, $notApprovedCount)
    {
        $this->count = $count;
        $this->approvedCount = $approvedCount;
        $this->semiApprovedCount = $semiApprovedCount;
        $this->notApprovedCount = $notApprovedCount;
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
        return [
            'data' => $this->collection, 
            'count' => $this->count,
            'approvedCount' => $this->approvedCount,
            'semiApprovedCount' => $this->semiApprovedCount,
            'notApprovedCount' => $this->notApprovedCount,
        ];
    }
}
