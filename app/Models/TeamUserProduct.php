<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamUserProduct extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable=
    [
        "user_id_creator",
        "team_user_id",
        "product_id"
    ];
}
