<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryOnesCreateRequest;
use App\Http\Requests\CategoryOnesEditRequest;
use App\Http\Resources\CategoryOnesCollection;
use App\Http\Resources\CategoryOnesResource;
use App\Models\CategoryOne;
use Exception;
use Log;

class CategoryOnesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $category_ones = CategoryOne::where('is_deleted', false)->orderBy('id', 'desc')->paginate(env('PAGE_COUNT'));
        return (new CategoryOnesCollection($category_ones))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CategoryOnesCreateRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryOnesCreateRequest $request)
    {

        $category_one = CategoryOne::create([
            'name' => $request->name,
        ]);
        return (new CategoryOnesResource($category_one))->additional([
            'error' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $category_one = CategoryOne::where('is_deleted', false)->find($id);
        if ($category_one != null) {
            return (new CategoryOnesResource($category_one))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new CategoryOnesResource($category_one))->additional([
            'error' => 'CategoryOnes not found!',
        ])->response()->setStatusCode(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\CategoryOnesEditRequest $request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CategoryOnesEditRequest $request, $id)
    {

        $category_one = CategoryOne::where('is_deleted', false)->find($id);
        if ($category_one != null) {
            $category_one->update($request->all());
            return (new CategoryOnesResource(null))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new CategoryOnesResource(null))->additional([
            'error' => 'CategoryOnes not found!',
        ])->response()->setStatusCode(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $category_one = CategoryOne::find($id);
        if ($category_one != null) {
            $category_one->is_deleted = 1;
            try {
                $category_one->save();
                return (new CategoryOnesResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in CategoryOnesController/destory', json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new CategoryOnesResource(null))->additional([
                        'error' => 'CategoryOnes deleting failed!' . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new CategoryOnesResource(null))->additional([
                        'error' => 'CategoryOnes deleting failed!',
                    ])->response()->setStatusCode(500);
                }

            }
        }
        return (new CategoryOnesResource(null))->additional([
            'error' => 'CategoryOnes not found!',
        ])->response()->setStatusCode(404);
    }
}
