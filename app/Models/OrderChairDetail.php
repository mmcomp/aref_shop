<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderChairDetail extends Model
{
    use HasFactory;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_details_id', 
        'chair_number',
        'price',
        'status'
    ];
    public function orderDetail()
    {
        $this->belongsTo('App\Models\OrderDetail','order_details_id', 'id');
    }
}
