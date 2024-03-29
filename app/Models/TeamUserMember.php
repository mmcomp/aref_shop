<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TeamUser;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamUserMember extends Model
{
    use HasFactory;
    use softDeletes;
    protected $table="team_user_members";
    protected $fillable=
    [
        "id",
        "team_user_id",
        "mobile",
        "is_verified"
    ];

    public function member()
    {
        return $this->hasOne('App\Models\User', 'email', 'mobile');
    }

    public function teamUser()
    {
        return $this->hasOne(TeamUser::class, 'id', 'team_user_id');
    }
}
