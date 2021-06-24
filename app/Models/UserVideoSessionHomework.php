<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVideoSessionHomework extends Model
{
    use HasFactory;
    protected $table = 'user_video_session_homeworks';

    protected $fillable = [
        'file',
        'user_video_sessions_id',
        'description',
        'teacher_description'
    ];

    public function userVideoSession()
    {
        return $this->hasOne('App\Models\UserVideoSession', 'id', 'user_video_sessions_id');
    }
}
