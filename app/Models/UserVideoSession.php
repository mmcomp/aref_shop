<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVideoSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_sessions_id',
        'users_id',
    ];
    public function videoSession()
    {
        return $this->hasOne('App\Models\VideoSession', 'id', 'video_sessions_id')->where('is_deleted', false);
    }
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'users_id')->where('is_deleted', false);
    }
    public function userVideoSessionHomework()
    {
        return $this->hasMany('App\Models\UserVideoSessionHomework','user_video_sessions_id','id');
    }
    public function productDetailVideo()
    {
        return $this->belongsTo('App\Models\ProductDetailVideo','video_sessions_id','video_sessions_id');
    }

}
