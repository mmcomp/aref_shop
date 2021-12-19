<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Audit extends Model
{
    use HasFactory;
    //protected $table="Audit";

    protected $fillable=[
        "id",
        "user_id",
        "user_name",
        "before",
        "after"
    ];
    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
