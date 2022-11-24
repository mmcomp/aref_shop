<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoSession extends Model
{
    use HasFactory;

    protected $fillable = [
        "start_date",
        "start_time",
        "end_time",
        "teacher_users_id",
        "price",
        "video_session_type",
        "video_link",
        "is_aparat"
    ];

    public function teacher()
    {
        return $this->belongsTo("App\Models\User", "teacher_users_id", "id");
    }
    public function videoSessionFiles()
    {
        return $this->hasMany("App\Models\VideoSessionFile", "video_sessions_id", "id");
    }
    public function userVideoSession()
    {
        return $this->hasOne("App\Models\UserVideoSession", "video_sessions_id", "id");
    }

    
}
