<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReadingStationUserStrike extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['reading_station_slut_user_id', 'reading_station_strike_id', 'reading_station_strike_score', 'description', 'day'];

    function readingStationStrike()
    {
        return $this->belongsTo(ReadingStationStrike::class);    
    }


    function readingStationSlutUser()
    {
        return $this->belongsTo(ReadingStationSlutUser::class);    
    }
}
