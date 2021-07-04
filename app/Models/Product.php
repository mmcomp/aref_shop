<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'name',
       'short_description',
       'long_description',
       'price',
       'sale_price',
       'sale_expire',
       'video_props',
       'category_ones_id',
       'category_twos_id',
       'category_threes_id',
       'main_image_path',
       'main_image_thumb_path',
       'second_image_path',
       'published',
       'type',
       'special',
       'education_system',
       'start_date',
       'days',
       'hour'
    ];

    public function categoryOnes()
    {
        return $this->hasOne('App\Models\CategoryOne', 'id', 'category_ones_id');
    }
    public function categoryTwos()
    {
        return $this->hasOne('App\Models\CategoryTwo', 'id', 'category_twos_id');
    }
    public function categoryThrees()
    {
        return $this->hasOne('App\Models\CategoryThree', 'id', 'category_threes_id');
    }
    public function productDetailVideos()
    {
        return $this->hasMany('App\Models\ProductDetailVideo', 'products_id', 'id')->join('video_sessions', 'video_sessions.id', '=', 'product_detail_videos.video_sessions_id')->select("product_detail_videos.*")->where('product_detail_videos.is_deleted', false)->where('video_sessions.is_deleted', false)->orderBy('video_sessions.start_date', 'asc')->orderBy('video_sessions.start_time', 'asc');
    }
   
    public function productDetailPackages()
    {
        return $this->hasMany('App\Models\ProductDetailPackage', 'products_id', 'id')->where('is_deleted', false);
    }
    public function productFiles()
    {
        return $this->hasMany('App\Models\ProductFile', 'products_id', 'id');
    }
    public function userProducts()
    {
        return $this->hasMany('App\Models\UserProduct', 'products_id', 'id');
    }
    public function orderDetail()
    {
        return $this->hasOne('App\Models\OrderDetail', 'products_id', 'id');
    }
    public function comments()
    {
        return $this->hasMany('App\Models\ProductComment', 'products_id', 'id');
    }

}
