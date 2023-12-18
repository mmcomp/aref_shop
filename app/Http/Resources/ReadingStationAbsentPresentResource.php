<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ReadingStationAbsentPresentResource extends JsonResource
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
                'reading_station_slut_user_exit_id' => $this->reading_station_slut_user_exit_id,
                // 'slutUserExit' => new ReadingStationSlutUsersResource($this->slutUserExit),
                'possible_end' => $this->possible_end,
                'end' => $this->end,
                'possible_exit_way' => $this->possible_exit_way,
                'exit_way' => $this->exit_way,
                'enter_way' => $this->enter_way,
                'attachment_address' => $this->attachment_address,
                'is_optional_visit' => $this->is_optional_visit,
                'is_processed' => $this->is_processed,
                'operator' => $this->operator ? [
                    "first_name" => $this->operator->first_name,
                    "last_name" => $this->operator->first_name,
                    "updated_at" => $this->updated_at,
                ] : null,
            ];
        }
    }
}
