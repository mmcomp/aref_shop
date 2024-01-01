<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReadingStationSlutUser extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'reading_station_weekly_program_id',
        'reading_station_slut_id',
        'day',
        'is_required',
        'status',
        'reading_station_absent_reason_id',
        'reading_station_absent_reason_score',
        'absense_approved_status',
        'user_id',
        'absense_file',
    ];

    function weeklyProgram()
    {
        return $this->hasOne(ReadingStationWeeklyProgram::class, 'id', 'reading_station_weekly_program_id');
    }

    function slut()
    {
        return $this->hasOne(ReadingStationSlut::class, 'id', 'reading_station_slut_id');
    }

    function absenseReason()
    {
        return $this->belongsTo(ReadingStationAbsentReason::class);
    }

    function absentPresent()
    {
        $user_id = $this->weeklyProgram->readingStationUser->user_id;
        $reading_station_id = $this->weeklyProgram->readingStationUser->reading_station_id;
        return $this->hasOne(ReadingStationAbsentPresent::class, 'day', 'day')
                    ->where('user_id', $user_id)
                    ->where('reading_station_id', $reading_station_id)
                    ->where('is_processed', 0);
    }

    function calls()
    {
        return $this->hasMany(ReadingStationCall::class);
    }

    function user()
    {
        return $this->belongsTo(User::class);    
    }

    function strikes()
    {
        return $this->hasMany(ReadingStationUserStrike::class);
    }
}
