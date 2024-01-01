<?php
namespace App\Utils;

use Illuminate\Pagination\LengthAwarePaginator;
use stdClass;

class CollectionPaginator extends LengthAwarePaginator {
    public $meta;

    function __construct($items, $total, $perPage, $currentPage = null, array $options = [])
    {
        parent::__construct($items, $total, $perPage, $currentPage, $options);
    }

    public function toArray()
    {
        $res = parent::toArray();
        $res['meta'] = ['last_page' => $res['last_page']];
        return $res;
    }
}
