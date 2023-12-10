<?php

namespace App\Http\Resources;

use App\Models\ReadingStationAbsentPresent;
use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationExitsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->resource) {

            return $this->resource->map(function (ReadingStationAbsentPresent $data) {
                return [
                    "table_number" => $data->user->readingStationUser->table_number,
                    "first_name" => $data->user->first_name,
                    "last_name" => $data->user->last_name,
                    "reading_station_slut_user_exit" => [
                        "name" => $data->slutUserExit->name,
                        "start" => $data->slutUserExit->start,
                        "end" => $data->slutUserExit->end,
                    ],
                    "exit_way" => $data->exit_way,
                    "possible_exit_way" => $data->possible_exit_way,
                    "possible_end" => $data->possible_end,
                    "exit_delay" => $data->exit_delay,
                    "calls" => $data->user->readingStationUser->calls ? count($data->user->readingStationUser->calls) : 0,
                    "exited" => $data->is_processed,
                ];
            });
        }
    }
}
