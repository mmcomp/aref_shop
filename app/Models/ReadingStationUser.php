<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReadingStationUser extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'reading_station_id', 
        'user_id', 
        'table_number', 
        'default_package_id', 
        'total', 
        'last_weekly_program', 
        'consultant', 
        'representative', 
        'contract_start', 
        'contract_end',
    ];

    function readingStation()
    {
        return $this->belongsTo(ReadingStation::class);
    }

    function user()
    {
        return $this->belongsTo(User::class);
    }

    function package()
    {
        return $this->belongsTo(ReadingStationPackage::class, 'default_package_id');
    }

    function weeklyPrograms()
    {
        $startOfCurrentWeek = Carbon::now()->startOfWeek(Carbon::SATURDAY)->toDateString();
        return $this->hasMany(ReadingStationWeeklyProgram::class)
            ->where('start', '>=', $startOfCurrentWeek);
    }

    function lastWeeklyProgram()
    {
        return $this->belongsTo(ReadingStationWeeklyProgram::class, 'last_weekly_program');
    }

    function allWeeklyPrograms()
    {
        return $this->hasMany(ReadingStationWeeklyProgram::class);
    }

    function noneZeroSlutWeeklyPrograms()
    {
        $startOfCurrentWeek = Carbon::now()->startOfWeek(Carbon::SATURDAY)->toDateString();
        return $this->hasMany(ReadingStationWeeklyProgram::class)
            ->where('start', '>=', $startOfCurrentWeek)
            ->whereHas('sluts', operator: '>', count: 0);
    }
}
