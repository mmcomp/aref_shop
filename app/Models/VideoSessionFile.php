<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoSessionFile extends Model
{
    use HasFactory;
    protected $fillable = [
        'video_sessions_id',
        'files_id',
        'users_id',
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'users_id');
    }
    public function videoSession()
    {
        return $this->hasOne('App\Models\VideoSession', 'id', 'video_sessions_id');
    }
    public function file()
    {
        return $this->hasOne('App\Models\File', 'id', 'files_id');
    }
}
