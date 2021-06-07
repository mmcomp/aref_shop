<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
      'orders_id', 
      'users_id',
      'price',
      'bank_returned',
      'ref_id',
      'res_code',
      'sale_order_id',
      'sale_reference_id',
      'bank_orders_id'
    ];

    public function user()
    {
       return $this->belongsTo('App\Models\User','users_id','id');  
    }

    public function order()
    {
       return $this->belongsTo('App\Models\Order','orders_id','id');  
    }
}
