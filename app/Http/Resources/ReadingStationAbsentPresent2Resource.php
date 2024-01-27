<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationAbsentPresent2Resource extends JsonResource
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
                'possible_end' => $this->possible_end,
                'end' => $this->end,
                'possible_exit_way' => $this->possible_exit_way,
                'exit_way' => $this->exit_way,
                'enter_way' => $this->enter_way,
                'attachment_address' => $this->attachment_address,
                'is_optional_visit' => $this->is_optional_visit,
                'is_processed' => $this->is_processed,
            ];
        }
    }
}
