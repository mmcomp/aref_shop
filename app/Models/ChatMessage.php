<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
       'users_id',
       'message',
       'video_sessions_id',
       'ip_address'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'users_id', 'id');
    }
    public function videoSession()
    {
        return $this->belongsTo('App\Models\VideoSession', 'video_sessions_id', 'id');
    }
}
