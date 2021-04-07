<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
       'name',
       'provinces_id'
    ];

    public function province()
    {
<<<<<<< HEAD
        return $this->hasOne('App\Models\Province','id','provinces_id');
    }
}
=======
        return $this->hasOne('App\Models\Province', 'id', 'provinces_id');
    }
    
}

>>>>>>> d4f944b8cdac5a7dc5dcdcf966d92545e703484f
