<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderVideoDetail extends Model
{
    use HasFactory;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_details_id', 
        'product_details_videos_id',
        'price'
    ];
    public function orderDetail()
    {
        return $this->belongsTo('App\Models\OrderDetail', 'order_details_id', 'id');
    }
    public function productDetailVideo()
    {
        return $this->belongsTo('App\Models\ProductDetailVideo', 'product_details_videos_id', 'id');
    }
}
