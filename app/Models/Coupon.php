<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "description",
        "amount",
        "type",
        "expired_at",
        "products_id"
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'products_id', 'id');
    }
    public function orderDetail()
    {
        return $this->hasOne('App\Models\OrderDetail', 'coupons_id', 'id');
    }
    // public function userCoupon()
    // {
    //     return $this->belongsTo('App\Models\UserCoupon', 'coupons_id', 'id');
    // }
}
