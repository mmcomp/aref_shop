<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductDetailChairsCreateRequest;
use App\Http\Requests\ProductDetailChairsEditRequest;
use App\Http\Resources\ProductDetailChairsCollection;
use App\Http\Resources\ProductDetailChairsResource;
use App\Models\ProductDetailChair;
use Exception;
use Log;

class ProductDetailChairsController extends Controller
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
            $product_detail_chairs = ProductDetailChair::where('is_deleted', false)->orderBy('id', 'desc')->get();
        } else {
            $product_detail_chairs = ProductDetailChair::where('is_deleted', false)->orderBy('id', 'desc')->paginate(env('PAGE_COUNT'));
        }
        return (new ProductDetailChairsCollection($product_detail_chairs))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Create & Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\ProductDetailChairsCreateRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductDetailChairsCreateRequest $request)
    {

        $product_detail_chair = ProductDetailChair::create($request->all());
        return (new ProductDetailChairsResource($product_detail_chair))->additional([
            'error' => null
        ])->response()->setStatusCode(201);
    }

   /**
     * Get productDetailChair and return its properties
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {

        $product_detail_chair = ProductDetailChair::where('is_deleted', false)->find($id);
        if ($product_detail_chair != null) {
            return (new ProductDetailChairsResource($product_detail_chair))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductDetailChairsResource($product_detail_chair))->additional([
            'error' => 'ProductDetailChair not found!',
        ])->response()->setStatusCode(404);

    }

    /**
     * Edit & Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id,ProductDetailChairsEditRequest $request)
    {
        $product_detail_chair = ProductDetailChair::where('is_deleted',false)->find($id);
        if ($product_detail_chair != null) {
            $product_detail_chair->update($request->all());
            return (new ProductDetailChairsResource(null))->additional([
                'error' => null
            ])->response()->setStatusCode(200);
        }
        return (new ProductDetailChairsResource(null))->additional([
            'error' => 'ProductDetailChairs not found!',
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
        $product_detail_chair = ProductDetailChair::where('is_deleted',false)->find($id);
        if ($product_detail_chair != null) {
            $product_detail_chair->is_deleted = 1;
            try {
                $product_detail_chair->save();
                return (new ProductDetailChairsResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in ProductDetailChairsController/destory', json_encode($e));
                if(env('APP_ENV') == 'development'){
                    return (new ProductDetailChairsResource(null))->additional([
                        'error' => 'failed in ProductDetailChairsController/destory', json_encode($e)
                    ])->response()->setStatusCode(500);
                } else if(env('APP_ENV') == 'production'){
                    return (new ProductDetailChairsResource(null))->additional([
                        'error' => 'failed in ProductDetailChairsController/destory'
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new ProductDetailChairsResource(null))->additional([
            'error' => 'ProductDetailChairs not found!',
        ])->response()->setStatusCode(404);
    }
}
