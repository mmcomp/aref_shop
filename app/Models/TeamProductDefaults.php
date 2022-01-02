<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class TeamProductDefaults extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table="team_product_defaults";
    protected $fillable=
    [
        "id",       
        "product_id"
    ];
}
