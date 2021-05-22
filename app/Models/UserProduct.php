<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'products_id',
        'users_id',
        'partial',
    ];
    public function product()
    {
        return $this->hasOne('App\Models\Product', 'id', 'products_id')->where('is_deleted', false);
    }
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'users_id')->where('is_deleted', false);
    }
}
