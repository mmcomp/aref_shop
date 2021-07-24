<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CategoryOne;
use App\Http\Resources\User\CategoryOnesCollection;


class CategoryOnesController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {

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
