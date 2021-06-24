<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVideoSessionHomework extends Model
{
    use HasFactory;

    protected $fillable = [
        'file',
        'user_video_sessions_id',
        'description',
        'teacher_users_id'
    ];

    public function userVideoSession()
    {
        return $this->hasOne('App\Models\UserVideoSession', 'id', 'user_video_sessions_id');
    }
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'teacher_users_id')->where('is_deleted', false);
    }
}
