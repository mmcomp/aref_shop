<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    public function menus()
    {
        return $this->hasMany('App\Models\GroupMenu', 'groups_id', 'id');
    }
}
