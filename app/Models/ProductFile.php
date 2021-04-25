<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFile extends Model
{
    use HasFactory;
    protected $fillable = [
        'products_id',
        'users_id',
        'files_id'
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'users_id');
    }
    public function product()
    {
        return $this->hasOne('App\Models\Product', 'id', 'products_id');
    }
    public function file()
    {
        return $this->hasOne('App\Models\File', 'id', 'files_id');
    }
}
