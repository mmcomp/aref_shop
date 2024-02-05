<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class UserResource extends JsonResource
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
                'email' => $this->email,
                'first_name' => $this->first_name == null ? "" : $this->first_name,
                'last_name' => $this->last_name,
                'avatar_path' => $this->avatar_path,
                'referrer_user' => new UserResource($this->referrerUser),
                'address' => $this->address,
                'postall' => $this->postall,
                'gender' => $this->gender,
                'national_code' => $this->national_code,
                'home_tell' => $this->home_tell,
                'father_cell' => $this->father_cell,
                'mother_cell' => $this->mother_cell,
                'grade' => $this->grade,
                'description' => $this->description,
                'is_reading_station_user' => $this->is_reading_station_user,
                'city' => new CityResource($this->city),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'group' => new GroupResource($this->group),
                'readingStationUser' => new ReadingStationUsers3Resource($this->readingStationUser),
                'readingStation' => new ReadingStationResource($this->readingStation),
                'disabled' => $this->disabled,
                'school' => $this->school,
                'major' => $this->major,
            ];
        }
    }
}
