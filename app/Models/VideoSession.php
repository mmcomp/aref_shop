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
    ];

    public function teacher()
    {
        return $this->belongsTo("App\Models\User", "teacher_users_id", "id");
    }
    public function video_session_files()
    {
        return $this->hasMany("App\Models\VideoSessionFile", "video_sessions_id", "id");
    }
}
