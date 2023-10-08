<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationUsersResource extends JsonResource
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
                'table_number' => $this->table_number,
                'user'=> new UserResource($this->user),
                'readingStation' => new ReadingStation2Resource($this->readingStation),
                'package' => new ReadingStationPackagesResource($this->package),
            ];
        }
    }
}
