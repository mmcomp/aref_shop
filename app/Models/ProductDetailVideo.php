<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetailVideo extends Model
{
    use HasFactory;
    
    protected $fillable = [
      'name',
      'start_date',
      'start_time',
      'end_time',
      'teacher_users_id',
      'products_id',
      'price',
      'video_session_type',
      'video_link',
      'is_hidden'     
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'products_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo('App\Models\User','users_id','id');
    }
}
