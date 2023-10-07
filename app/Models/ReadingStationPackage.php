<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReadingStationPackage extends Model
{
    use HasFactory;    
    use SoftDeletes;

    protected $fillable = ['name', 'required_time', 'optional_time'];
}
