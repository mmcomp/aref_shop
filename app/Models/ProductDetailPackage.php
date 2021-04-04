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
        
        return $this->hasOne('App\Models\Product', 'id', 'products_id');
    }
    public function childproduct()
    {

        return $this->hasOne('App\Models\Product', 'id', 'child_products_id');
    }
}
