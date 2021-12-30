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

    public function leader()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id_creator')->select('id', 'email', 'first_name', 'last_name')->where('is_deleted', false);
    }
}
