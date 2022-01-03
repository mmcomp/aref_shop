<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TeamUserMemmber;

class TeamUser extends Model
{
    use HasFactory;
    use softDeletes;
    //use TeamUserMemmber;
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
    public function TeamMember()
    {
        return $this->hasMany('App\Models\TeamUserMemmber',"team_user_id","id");
    }
}
