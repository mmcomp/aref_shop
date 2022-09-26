<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVideoSessionPresent extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_sessions_id',
        'users_id',
        'online_started_at',
        'online_exited_at',
        'online_spend',
        'offline_started_at',
        'offline_exited_at',
        'offline_spend'
    ];
    public function videoSession()
    {
        return $this->hasOne('App\Models\VideoSession', 'id', 'video_sessions_id')->where('is_deleted', false);
    }
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'users_id')->where('is_deleted', false);
    }
}
