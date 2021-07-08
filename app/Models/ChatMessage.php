<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    public $timestamps = ["created_at"]; //only want to used created_at column
    const UPDATED_AT = null; //and updated by default null set

    protected $fillable = [
        'message',
        'users_id',
        'video_sessions_id'
    ];
    public function user()
    {
        return $this->belongsTo('App\Models\User','users_id', 'id');
    }
    public function videoSession()
    {
        return $this->belongsTo('App\Models\VideoSession', 'video_sessions_id', 'id');
    }
}
