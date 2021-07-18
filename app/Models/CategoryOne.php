<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryOne extends Model
{
    use HasFactory;

    protected $fillable = ['name','image_path', 'published'];

    public function categoryTwos()
    {
        return $this->hasMany('App\Models\CategoryTwo', 'category_ones_id', 'id')->where('is_deleted', false);
    }
}
