<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description'
    ];

    public function menus()
    {
        return $this->hasMany('App\Models\GroupMenu', 'groups_id', 'id');
    }
    
    public function gates()
    {
        return $this->hasMany('App\Models\GroupGate','groups_id','id');
    }
}
