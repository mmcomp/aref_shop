<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationSlutUserLatesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        if ($this->resource != null) {
            return [
                'day' => $this->day,
                'count' => $this->count,
                'score' => $this->score,
                'minutes' => $this->minutes,
                'details' => $this->details,
            ];
        }
    }
}
