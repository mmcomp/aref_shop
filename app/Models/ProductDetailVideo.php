<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetailVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'products_id',
        'price',
        'video_sessions_id',
        'extraordinary',
        'single_purchase',
        'is_hidden',
        'free_conference_start_mode',
        'free_conference_description',
        'free_conference_before_start_text',
        'free_hidden'
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'products_id', 'id')->where('is_deleted', false);
    }

    public function videoSession()
    {
        return $this->belongsTo('App\Models\VideoSession', 'video_sessions_id', 'id')->where('is_deleted', false);
    }
    public function orderVideoDetail()
    {
        return $this->hasOne('App\Models\OrderVideoDetail', 'product_detail_videos_id', 'id');
    }
    public function userVideoSession()
    {
        return $this->hasMany('App\Models\UserVideoSession',"video_sessions_id","video_sessions_id");
    }
}
