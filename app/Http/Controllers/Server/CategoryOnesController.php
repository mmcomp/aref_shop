<?php

namespace App\Http\Controllers\Server;

use App\Models\CategoryOne;
use App\Http\Resources\CategoryOnesCollection;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryOnesResource;

class CategoryOnesController extends Controller
{

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {

        if(request()->ip() != env('WORDPRESS_IP_SHOP')){
            return (new CategoryOnesResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(404);
        }
        $per_page = request()->get('per_page');
        $category_ones_builder = CategoryOne::where('is_deleted', false)->where('published', 1)->orderBy('ordering', 'asc');
        if ($per_page == "all") {
            $category_ones = $category_ones_builder->get();
        } else {
            $category_ones = $category_ones_builder->paginate(env('PAGE_COUNT'));
        }
        return (new CategoryOnesCollection($category_ones))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
