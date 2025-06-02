<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReadingStationSlut extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['reading_station_id', 'name', 'start', 'end', 'duration', 'is_sleep'];

    function readingStation()
    {
        return $this->belongsTo(ReadingStation::class);
    }

    function previesSlut()
    {
        return $this->where('id', '!=', $this->id)
            ->where('start', '<', $this->start)
            ->orderBy('start', 'desc')
            ->first();
    }
}
