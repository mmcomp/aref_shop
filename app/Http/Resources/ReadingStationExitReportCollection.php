<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ReadingStationExitReportCollection extends ResourceCollection
{
    private $count;
    function __construct($resource, $count)
    {
        $this->count = $count;
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
        return ['data' => $this->collection, 'count' => $this->count];
    }
}
