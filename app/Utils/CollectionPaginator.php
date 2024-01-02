<?php

namespace App\Utils;

use Illuminate\Pagination\LengthAwarePaginator;
use stdClass;

class CollectionPaginator extends LengthAwarePaginator
{
    protected $totalValue = 0;

    public function __construct($items, $total, $perPage, $totalValue, $currentPage = null, array $options = [])
    {
        $this->totalValue = $totalValue;
        parent::__construct($items, $total, $perPage, $currentPage, $options);
    }

    public function toArray()
    {
        $res = parent::toArray();
        $res['meta'] = ['last_page' => $res['last_page']];
        $res['total_value'] = $this->totalValue;
        return $res;
    }
}
