<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductComment extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'users_id',
        'products_id',
        'comment'
    ];
    public function product()
    {
        return $this->hasOne('App\Models\Product', 'id', 'products_id');
    }
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'users_id');
    }
}
