<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationUsers5Resource extends JsonResource
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
                'id' => $this->user->id,
                'reading_station_user_id' => $this->id,
                'table_number' => $this->table_number,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'status' => $this->status,
                'reading_station_name' => $this->readingStation->name,
            ];
        }
    }
}
