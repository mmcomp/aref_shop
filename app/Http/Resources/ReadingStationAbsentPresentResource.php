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
                // 'slutUserExit' => new ReadingStationSlutUsersResource($this->slutUserExit),
                'possible_end' => $this->possible_end,
                'end' => $this->end,
                'posssible_exit_way' => $this->posssible_exit_way,
                'exit_way' => $this->exit_way,
                'enter_way' => $this->enter_way,
                'attachment_address' => $this->attachment_address,
                'is_optional_visit' => $this->is_optional_visit,
                'is_processed' => $this->is_processed,
            ];
        }
    }
}
