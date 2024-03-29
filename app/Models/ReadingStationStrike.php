<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReadingStationStrike extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'score', 'is_point'];
}
