<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamUserMemmber extends Model
{
    use HasFactory;
    protected $table="team_user_members";
    protected $fillable=
    [
        "id",
        "team_user_id",
        "mobile",
        "is_verified"
    ];
}
