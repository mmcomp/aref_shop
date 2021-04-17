<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "description",
        "amount",
        "type",
        "expired_at",
        "products_id"
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'products_id', 'id');
    }
}
