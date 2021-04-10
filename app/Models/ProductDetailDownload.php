<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetailDownload extends Model
{
    use HasFactory;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'products_id',
        'file_path'         
     ];
    public function product()
    {
        return $this->hasOne('App\Models\Product', 'id', 'products_id');
    }
}
