<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
       'name',
       'provinces_id'
    ];

    public function province()
    {
        return $this->hasOne('App\Models\Province', 'id', 'provinces_id');
    }
    
}
