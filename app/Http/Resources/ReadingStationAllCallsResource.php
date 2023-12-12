<?php

namespace App\Http\Resources;


class ReadingStationAllCallsResource extends ReadingStationNeededCallsResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->processSlutUser();
    }
}
