<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReadingStationUser extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['reading_station_id', 'user_id', 'table_number'];

    function readingStation()
    {
        return $this->belongsTo(ReadingStation::class);
    }

    function user()
    {
        return $this->belongsTo(User::class);
    }
}
