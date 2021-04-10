<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetailChair extends Model
{
    use HasFactory;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'products_id',
        'start',
        'end',
        'price',
        'description',
     ];
    public function product()
    {
        return $this->hasOne('App\Models\Product', 'id', 'products_id');
    }
}
