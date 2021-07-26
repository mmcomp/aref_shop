<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;
    protected $fillable = [
       'orders_id', 
       'products_id',
       'product_detail_videos_id',
       'users_id',
       'description'
    ];

    public function Order()
    {
        return $this->hasOne('App\Models\Order', 'id', 'orders_id');
    }
    public function Product()
    {
        return $this->hasOne('App\Models\Product', 'id', 'products_id');
    }
    public function ProductDetailVideo()
    {
        return $this->hasOne('App\Models\ProductDetailVideo', 'id', 'product_detail_videos_id');
    }
    public function User()
    {
        return $this->hasOne('App\Models\User', 'id', 'users_id');
    }
}
