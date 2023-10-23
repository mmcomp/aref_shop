<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'posssible_exit_way',
        'exit_way',
        'enter_way',
        'attachment_address',
        'is_optional_visit',
        'is_processed',
    ];

    public function slutUserExit()
    {
        return $this->belongsTo(ReadingStationSlutUser::class, 'reading_station_slut_user_exit_id');
    }
}
