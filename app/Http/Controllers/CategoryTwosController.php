<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryTwosCreateRequest;
use App\Http\Requests\CategoryTwosEditRequest;
use App\Http\Resources\CategoryTwosCollection;
use App\Http\Resources\CategoryTwosResource;
use App\Models\CategoryTwo;
use Exception;
use Illuminate\Http\Request;
use Log;

class CategoryTwosController extends Controller
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
            $category_twos = CategoryTwo::where('is_deleted', false)->orderBy('id', 'desc')->get();
        } else {
            $category_twos = CategoryTwo::where('is_deleted', false)->orderBy('id', 'desc')->paginate(env('PAGE_COUNT'));
        }
        return (new CategoryTwosCollection($category_twos))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CategoryTwosCreateRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CategoryTwosCreateRequest $request)
    {

        $category_two = CategoryTwo::create([
            'name' => $request->name,
            'category_ones_id' => $request->category_ones_id
        ]);
        return (new CategoryTwosResource($category_two))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {

        $category_two = CategoryTwo::where('is_deleted', false)->find($id);
        if ($category_two != null) {
            return (new CategoryTwosResource($category_two))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new CategoryTwosResource($category_two))->additional([
            'errors' => ['category_two' => ['CategoryTwos not found!']],
        ])->response()->setStatusCode(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\CategoryTwosEditRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CategoryTwosEditRequest $request, $id)
    {

        $category_two = CategoryTwo::where('is_deleted', false)->find($id);
        if ($category_two != null) {
            $category_two->update($request->all());
            return (new CategoryTwosResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new CategoryTwosResource(null))->additional([
            'errors' => ['category_two' => ['CategoryTwos not found!']],
        ])->response()->setStatusCode(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {

        $category_two = CategoryTwo::find($id);
        if ($category_two != null) {
            $category_two->is_deleted = 1;
            try {
                $category_two->save();
                return (new CategoryTwosResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in CategoryTwosController/destory', json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new CategoryTwosResource(null))->additional([
                        'errors' => ['fail' => ['CategoryTwos deleting failed!' . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new CategoryTwosResource(null))->additional([
                        'errors' => ['fail' => ['CategoryTwos deleting failed!']],
                    ])->response()->setStatusCode(500);
                }

            }
        }
        return (new CategoryTwosResource(null))->additional([
            'errors' => ['category_two' => ['CategoryTwos not found!']],
        ])->response()->setStatusCode(404);
    }
    /**
     * Get subset of categoryTwo
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function GetSubsetOfCategoryTwo($id)
    {

        $category_two = CategoryTwo::where('is_deleted', false)->find($id);
        if ($category_two != null) {
            return (new CategoryTwosCollection($category_two->categoryThrees))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new CategoryTwosResource(null))->additional([
            'errors' => ['category_two' => ['CategoryTwos not found!']],
        ])->response()->setStatusCode(404);

    }
}
