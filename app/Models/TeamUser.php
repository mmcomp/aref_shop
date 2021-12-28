<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamUser extends Model
{
    use HasFactory;
    protected $table="team_users";
    protected $fillable=
    [
        "user_id_creator",
        "name"
        //"is_full"
    ];
}
