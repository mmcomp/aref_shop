<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationExitReportResource extends JsonResource
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
                'table_number' => $this->user->readingStationUser->table_number,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'day' => $this->day,
                'slut' => new ReadingStationSluts2Resource($this->slutUserExit),
                'end' => $this->end,
                'possible_exit_way' => $this->possible_exit_way,
                'exit_delay' => $this->exit_delay,
                'exit_way' => $this->exit_way,
                'status' => $this->is_processed,
            ];
        }
    }
}
