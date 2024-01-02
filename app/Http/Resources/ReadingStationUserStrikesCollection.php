<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ReadingStationUserStrikesCollection extends ResourceCollection
{
    protected $total;
    public function __construct($resource, $total)
    {
        parent::__construct($resource);
        $this->total = $total;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [ 'data' => $this->collection, 'total_value' => $this->total];
    }
}
