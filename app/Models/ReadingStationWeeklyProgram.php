<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadingStationWeeklyProgram extends Model
{
    use HasFactory;
    protected $fillable = ['reading_station_user_id', 'name', 'required_time', 'optional_time', 'is_verified', 'start', 'end', 'required_time_done', 'optional_time_done', 'strikes_done', 'absence_done'];

    function sluts()
    {
        return $this->hasMany(ReadingStationSlutUser::class);   
    }
}
