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
        if ($per_page == "all") {
            $category_ones = CategoryOne::where('is_deleted', false)->where('published', 1)->orderBy('id', 'desc')->get();
        } else {
            $category_ones = CategoryOne::where('is_deleted', false)->where('published', 1)->orderBy('id', 'desc')->paginate(env('PAGE_COUNT'));
        }
        return (new CategoryOnesCollection($category_ones))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
