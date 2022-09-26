<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSync extends Model
{
    use HasFactory;
    protected $fillable = [
        "status",
        "products_id",
        "error_message",
    ];

    public function user()
    {
        return $this->hasOne('App\Models\Product', 'id', 'products_id')->where('is_deleted', false);
    }
}
