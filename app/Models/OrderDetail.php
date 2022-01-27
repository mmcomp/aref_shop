<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'orders_id',
        'products_id',
        'price',
        'coupons_id',
        'coupons_amount',
        'users_id',
        'all_videos_buy',
        'status',
        'number',
        'total_price',
        'total_price_with_coupon'
    ];
    public function order()
    {
        return $this->belongsTo('App\Models\Order', 'orders_id', 'id');
    }
    public function product()
    {
        return $this->hasOne('App\Models\Product', 'id', 'products_id')->where('is_deleted', false);
    }
    public function coupon()
    {
        return $this->hasOne('App\Models\Coupon', 'id', 'coupons_id');
    }
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'users_id', 'id');
    }
    public function orderVideoDetails()
    {
        return $this->hasMany('App\Models\OrderVideoDetail', 'order_details_id', 'id');
    }
    public function orderChairDetails()
    {
        return $this->hasMany('App\Models\OrderChairDetail', 'order_details_id', 'id');
    }
    public function OrderPackageDetail()
    {
        return $this->hasMany('App\Models\OrderPackageDetail', 'order_details_id', 'id');
    }
    public function refund()
    {
        return $this->hasOne('App\Models\Refund', 'order_details_id', 'id');
    }
}
