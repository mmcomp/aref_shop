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
        if ($per_page == "all") {
            $category_ones = CategoryOne::where('is_deleted', false)->where('published', 1)->orderBy('ordering', 'asc')->get();
        } else {
            $category_ones = CategoryOne::where('is_deleted', false)->where('published', 1)->orderBy('ordering', 'asc')->paginate(env('PAGE_COUNT'));
        }
        return (new CategoryOnesCollection($category_ones))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
