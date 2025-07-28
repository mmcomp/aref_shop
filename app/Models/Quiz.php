<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function getQuiz()
    {
        $this->id = $this->exam_id;

        return $this;
    }

    public function fromQuiz(array $quiz): Quiz
    {
        $entity = Quiz::where('exam_id', $quiz['id'])->first();
        if (!$entity) {
            $entity = new Quiz();
            $entity->exam_id = $quiz['id'];
        }
        $entity->examCode = $quiz['examCode'];
        $entity->questionCount = $quiz['questionCount'];
        $entity->fileName = $quiz['fileName'];
        $entity->questionfileName = $quiz['questionfileName'];
        $entity->answerfileName = $quiz['answerfileName'];
        $entity->keyfileName = $quiz['keyfileName'];
        $entity->entryDate = $quiz['entryDate'];
        $entity->startDate = $quiz['startDate'];
        $entity->examTime = $quiz['examTime'];
        $entity->startDateGregorian = $quiz['startDateGregorian'];
        $entity->endDateGregorian = $quiz['endDateGregorian'];
        $entity->title = $quiz['title'];
        $entity->save();
        return $entity;
    }
}
