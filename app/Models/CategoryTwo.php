<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryTwo extends Model
{
    use HasFactory;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','category_ones_id'];

    public function category_one()
    {
        return $this->hasOne('App\Models\CategoryOne', 'id', 'category_ones_id');
    }

}
