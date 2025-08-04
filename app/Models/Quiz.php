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

    public function files()
    {
        return $this->hasMany(QuizFile::class);
    }

    public function fromQuiz(array $quiz): Quiz
    {
        $entity = Quiz::where('exam_id', $quiz['id'])->first();
        if (!$entity) {
            $entity = new Quiz();
            $entity->exam_id = $quiz['id'];
        }
        $entity->examCode = $quiz['examCode'];
        $entity->questionCount = $quiz['questionCount'] ?? $entity->questionCount;
        $entity->fileName = $quiz['fileName'] ?? $entity->fileName;
        $entity->questionfileName = $quiz['questionfileName'] ?? $entity->questionfileName;
        $entity->answerfileName = $quiz['answerfileName'] ?? $entity->answerfileName;
        $entity->keyfileName = $quiz['keyfileName'] ?? $entity->keyfileName;
        $entity->entryDate = $quiz['entryDate'] ?? $entity->entryDate;
        $entity->startDate = $quiz['startDate'] ?? $entity->startDate;
        $entity->examTime = $quiz['examTime'] ?? $entity->examTime;
        $entity->startDateGregorian = $quiz['startDateGregorian'] ?? $entity->startDateGregorian;
        $entity->endDateGregorian = $quiz['endDateGregorian'] ?? $entity->endDateGregorian;
        $entity->title = $quiz['title'] ?? $entity->title;
        $entity->save();


        if (isset($quiz['answerDocuments'])) {
            foreach ($quiz['answerDocuments'] as $file) {
                $entity->files()->create([
                    'file_id' => $file['Id'],
                    'title' => $file['Title'],
                    'url' => $file['DocFileUrl'],
                    'access_time' => $file['AccessDateTime'],
                ]);
            }
        }

        return $entity;
    }
}
