<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ListOfVideosOfAProductResource extends JsonResource
{
    protected $value;
    public function foo($value)
    {
        $this->foo = $value;
        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->resource != null) {
            $bought = false;
            if ($this->videoSession) {
                if ($this->videoSession->userVideoSession) {
                    if ($this->videoSession->userVideoSession->users_id == Auth::user()->id) {
                        $bought = true;
                    }
                }
            }
            return [
                'id' => $this->id,
                'buyed_before' => $bought,
                'start_date' => $this->videoSession ? $this->videoSession->start_date : null,
                'start_time' => $this->videoSession ? date('H:i', strtotime($this->videoSession->start_time)) : null,
                'end_time' => $this->videoSession ? date('H:i', strtotime($this->videoSession->end_time)) : null,
                'name' => $this->name,
                'is_hidden' => $this->is_hidden,
                'price' => $this->price == null ? ($this->videoSession ? $this->videoSession->price : null) : $this->price,
            ];
        }
    }
}
