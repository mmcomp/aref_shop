<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationResource extends JsonResource
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
                'table_start_number' => $this->table_start_number,
                'table_end_number' => $this->table_end_number,
                'offdays' => new ReadingStationOffdays2Collection($this->offdays),
                'sluts' => new ReadingStationSluts2Collection($this->sluts),
                // 'users' => new ReadingStationUsers2Collection($this->users),
            ];
        }
    }
}
