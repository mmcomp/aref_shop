<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuiz extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function oldQuiz()
    {
        return $this->hasOne(Quiz::class, 'examCode', 'quiz_id');
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class, 'id', 'quiz_id');
    }
}
