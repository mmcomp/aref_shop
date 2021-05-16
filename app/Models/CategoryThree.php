<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryThree extends Model
{
    use HasFactory;
      /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','category_twos_id'];

    public function category_two()
    {
        return $this->hasOne('App\Models\CategoryTwo', 'id', 'category_twos_id')->where('is_deleted', false);
    }
}
