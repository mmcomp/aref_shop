<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'users_id',
        'amount',
        'comment',
        'status'
    ];
    public function user()
    {
        return $this->belongsTo('App\Models\User','users_id', 'id');
    }
    public function orderDetail()
    {
        return $this->hasOne('App\Models\OrderDetail', 'id', 'orders_id');
    }
}
