<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
