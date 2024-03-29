<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReadingStation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'table_start_number', 'table_end_number'];

    function offdays()
    {
        return $this->hasMany(ReadingStationOffday::class);
    }

    function sluts()
    {
        return $this->hasMany(ReadingStationSlut::class);
    }

    function users()
    {
        return $this->hasMany(ReadingStationUser::class);
    }
}
