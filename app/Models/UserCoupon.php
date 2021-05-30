<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCoupon extends Model
{
    use HasFactory;
    protected $fillable = [
        'users_id',
        'coupons_id'
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User','id','users_id')->where('is_deleted',false);
    }

    public function coupon()
    {
        return $this->hasOne('App\Models\Coupon','id','coupons_id')->where('is_deleted',false);
    }
}
