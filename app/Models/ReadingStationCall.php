<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadingStationCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'reading_station_slut_user_id',
        'reason',
        'answered',
        'description',
        'caller_user_id',
    ];

    function slutUser()
    {
        return $this->hasOne(ReadingStationSlutUser::class, 'id', 'reading_station_slut_user_id');
    }
}
