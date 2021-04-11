<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetailPackage extends Model
{
    use HasFactory;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'products_id',
        'child_products_id'         
     ];
    public function product()
    {
        
        return $this->belongsTo('App\Models\Product', 'products_id', 'id');
    }
    public function childproduct()
    {

        return $this->belongsTo('App\Models\Product', 'child_products_id', 'id');
    }
}
