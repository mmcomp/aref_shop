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
    public function orderDetails()
    {
        return $this->hasMany('App\Models\OrderDetail', 'orders_id', 'id');
    }
    public function payments()
    {
        return $this->hasMany('App\Models\Payment', 'orders_id', 'id')->where('is_deleted', false)->where('status', 'success');
    }
}
