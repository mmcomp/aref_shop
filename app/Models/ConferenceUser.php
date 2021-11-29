<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConferenceUser extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_detail_videos_id',
        'users_id',
        'referrer',
        'already_registerd'
     ];
}
