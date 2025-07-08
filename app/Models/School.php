<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ["id"];

    function city()
    {
        return $this->belongsTo(City::class);
    }

    function users()
    {
        return $this->hasMany(User::class);
    }
}
