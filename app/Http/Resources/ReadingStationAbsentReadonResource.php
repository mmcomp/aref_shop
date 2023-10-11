<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationAbsentReasonResource extends JsonResource
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
                'id' => $this->id,
                'name' => $this->name,
                'score' => $this->score,
            ];
        }
    }
}
