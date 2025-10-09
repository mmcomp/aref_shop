<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;


class QuizzResource extends JsonResource
{
    // protected $value;

    // public function foo($value)
    // {
    //     $this->foo = $value;
    //     return $this;
    // }
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
                'exam_id' => $this->exam_id,
                'examCode' => $this->examCode,
                'title' => $this->title,
                'questionCount' => $this->questionCount,
                'fileName' => $this->fileName,
                'questionfileName' => $this->questionfileName,
                'answerfileName' => $this->answerfileName,
                'keyfileName' => $this->keyfileName,
                'entryDate' => $this->entryDate,
                'startDate' => $this->startDate,
                'examTime' => $this->examTime,
                'startDateGregorian' => $this->startDateGregorian,
                'endDateGregorian' => $this->endDateGregorian,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }
    }
}
