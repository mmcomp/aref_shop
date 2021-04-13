<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupGate extends Model
{
    use HasFactory;


    protected $fillable = [
        'groups_id',
        'users_id',
        'key'
    ];

    public function group()
    {
        return $this->hasMany('App\Models\Group', 'id', 'groups_id');
    }

    public function user()
    {
        return $this->hasMany('App\Models\User', 'id', 'users_id');
    }
}
