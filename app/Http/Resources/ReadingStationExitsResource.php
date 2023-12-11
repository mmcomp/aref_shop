<?php

namespace App\Http\Resources;

use App\Models\ReadingStationAbsentPresent;
use Carbon\Carbon;
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
                $calls = 0;
                $data->user->readingStationUser->weeklyPrograms->map(function ($weeklyProgram) use (&$calls) {
                    $weeklyProgram->sluts->map(function ($slut) use (&$calls) {
                        $calls += count($slut->calls->whereDate('updated_at', Carbon::now()->toDateString()));
                    });
                });
                return [
                    "id" => $data->id,
                    "table_number" => $data->user->readingStationUser->table_number,
                    "first_name" => $data->user->first_name,
                    "last_name" => $data->user->last_name,
                    "reading_station_slut_user_exit" => $data->slutUserExit ? [
                        "name" => $data->slutUserExit->name,
                        "start" => $data->slutUserExit->start,
                        "end" => $data->slutUserExit->end,
                    ] : null,
                    "exit_way" => $data->exit_way,
                    "possible_exit_way" => $data->possible_exit_way,
                    "possible_end" => $data->possible_end,
                    "exit_delay" => $data->exit_delay,
                    "calls" => $calls,
                    "exited" => $data->is_processed,
                ];
            });
        }
    }
}
