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
       'type'
    ];

    public function category_ones()
    {
        return $this->hasOne('App\Models\CategoryOne', 'id', 'category_ones_id');
    }
    public function category_twos()
    {
        return $this->hasOne('App\Models\CategoryTwo', 'id', 'category_twos_id');
    }
    public function category_threes()
    {
        return $this->hasOne('App\Models\CategoryThree', 'id', 'category_threes_id');
    }
    public function product_detail_videos()
    {
        return $this->hasMany('App\Models\ProductDetailVideo', 'products_id', 'id')->join('video_sessions', 'video_sessions.id', '=', 'product_detail_videos.video_sessions_id')->orderBy('video_sessions.start_date', 'desc')->orderBy('video_sessions.start_time', 'desc');
    }

}
