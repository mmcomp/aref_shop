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
        'absense_approved_status'
    ];

    function weeklyProgram()
    {
        return $this->belongsTo(ReadingStationWeeklyProgram::class);
    }

    function slut()
    {
        return $this->belongsTo(ReadingStationSlut::class);
    }

    function absenseReason()
    {
        return $this->belongsTo(ReadingStationAbsentReason::class);
    }
}
