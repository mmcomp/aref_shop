<?php

namespace App\Utils;

use Illuminate\Pagination\LengthAwarePaginator;

class CollectionNoTotalPaginator extends LengthAwarePaginator
{
    public function toArray()
    {
        $res = parent::toArray();
        $res['meta'] = ['last_page' => $res['last_page']];
        return $res;
    }
}
