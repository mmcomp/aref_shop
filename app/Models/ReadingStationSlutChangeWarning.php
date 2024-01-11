<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReadingStationSlutChangeWarning extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'reading_station_slut_user_id',
        'description',
        'operator_id',
        'is_read',
        'reader_id',
    ];


    function readingStationSlutUser()
    {
        return $this->belongsTo(ReadingStationSlutUser::class);
    }
}
