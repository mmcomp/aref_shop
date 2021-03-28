<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMenu extends Model
{
    use HasFactory;

    public function group()
    {
        return $this->hasOne('App\Models\Group', 'id', 'groups_id');
    }

    public function menu()
    {
        return $this->hasOne('App\Models\Menu', 'id', 'menus_id');
    }
}
