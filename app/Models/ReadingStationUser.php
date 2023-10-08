<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReadingStationUser extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['reading_station_id', 'user_id', 'table_number', 'default_package_id'];

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
}
