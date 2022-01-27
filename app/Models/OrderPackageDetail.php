<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPackageDetail extends Model
{
    use HasFactory;
    protected $fillable=[
        "order_package_details",
        "product_child_id"
    ];
    public function OrderDetail()
    {
        return $this->belongsTo('App\Models\OrderDetail', 'order_details_id', 'id');
    }
    public function Product()
    {
        return $this->belongsTo('App\Models\OrderDetail', 'Product', 'product_child_id');
    }
}
