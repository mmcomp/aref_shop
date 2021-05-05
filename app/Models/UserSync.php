<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSync extends Model
{
    use HasFactory;
    protected $fillable = [
        "status",
        "users_id",
        "error_message",
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'users_id')->where('is_deleted', false);
    }
}
