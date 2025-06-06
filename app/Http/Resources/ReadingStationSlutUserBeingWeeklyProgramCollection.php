<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ReadingStationSlutUserBeingWeeklyProgramCollection extends ResourceCollection
{
    protected $total;
    protected $unCompletedWeeklyPrograms = [];
    public function __construct($resource, $total, $unCompletedWeeklyPrograms = [])
    {
        parent::__construct($resource);
        $this->total = $total;
        $this->unCompletedWeeklyPrograms = $unCompletedWeeklyPrograms;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        foreach($this->collection as $indx => $data) {
            $data->uncompleted = false;
            // dump($data);
            // dump($this->unCompletedWeeklyPrograms);
            // dump(in_array($data->id, $this->unCompletedWeeklyPrograms));
            if (in_array($data->id, $this->unCompletedWeeklyPrograms)) {
                $data->uncompleted = true;
            }

            $this->collection[$indx] = $data;
        }
        return [ 'data' => $this->collection, 'total_value' => $this->total];
    }
}
