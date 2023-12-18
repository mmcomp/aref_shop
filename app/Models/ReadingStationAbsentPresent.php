<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReadingStationAbsentPresent extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'reading_station_id',
        'day',
        'reading_station_slut_user_exit_id',
        'possible_end',
        'end',
        'possible_exit_way',
        'exit_way',
        'enter_way',
        'attachment_address',
        'is_optional_visit',
        'is_processed',
        'exit_delay',
        'operator_id',
    ];

    public function slutUserExit()
    {
        return $this->belongsTo(ReadingStationSlut::class, 'reading_station_slut_user_exit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);    
    }

    public function calls()
    {
        return $this->hasMany(ReadingStationCall::class);    
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');    
    }
}
