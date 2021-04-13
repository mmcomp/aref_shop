<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryThreesCreateRequest;
use App\Http\Requests\CategoryThreesEditRequest;
use App\Http\Resources\CategoryThreesCollection;
use App\Http\Resources\CategoryThreesResource;
use App\Models\CategoryThree;
use Exception;
use Log;

class CategoryThreesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $per_page = request()->get('per_page');
        if ($per_page == "all") {
            $category_threes = CategoryThree::where('is_deleted', false)->orderBy('id', 'desc')->get();
        } else {
            $category_threes = CategoryThree::where('is_deleted', false)->orderBy('id', 'desc')->paginate(env('PAGE_COUNT'));
        }
        return (new CategoryThreesCollection($category_threes))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CategoryThreesCreateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryThreesCreateRequest $request)
    {

        $category_one = CategoryThree::create([
            'name' => $request->name,
        ]);
        return (new CategoryThreesResource($category_one))->additional([
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

        $category_three = CategoryThree::where('is_deleted', false)->find($id);
        if ($category_three != null) {
            return (new CategoryThreesResource($category_three))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new CategoryThreesResource($category_three))->additional([
            'error' => 'CategoryThrees not found!',
        ])->response()->setStatusCode(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\CategoryThreesEditRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CategoryThreesEditRequest $request, $id)
    {

        $category_three = CategoryThree::where('is_deleted', false)->find($id);
        if ($category_three != null) {
            $category_three->update($request->all());
            return (new CategoryThreesResource(null))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new CategoryThreesResource(null))->additional([
            'error' => 'CategoryThrees not found!',
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

        $category_three = CategoryThree::where('is_deleted', false)->find($id);
        if ($category_three != null) {
            $category_three->is_deleted = 1;
            try {
                $category_three->save();
                return (new CategoryThreesResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in CategoryThreesController/destory', json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new CategoryThreesResource(null))->additional([
                        'error' => 'CategoryThrees deleting failed!' . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new CategoryThreesResource(null))->additional([
                        'error' => 'CategoryThrees deleting failed!',
                    ])->response()->setStatusCode(500);
                }

            }
        }
        return (new CategoryThreesResource(null))->additional([
            'error' => 'CategoryThrees not found!',
        ])->response()->setStatusCode(404);
    }
}
