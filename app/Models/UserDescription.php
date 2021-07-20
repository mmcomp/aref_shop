<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDescription extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_video_session_homeworks_id',
        'description',
        'users_id'
    ];

    public function userVideoSessionHomework()
    {
        return $this->hasOne('App\Models\UserVideoSessionHomework', 'id', 'user_video_session_homeworks_id');
    }
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'users_id');
    }
}
