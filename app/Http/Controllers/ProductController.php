<?php

namespace App\Http\Controllers;

use Exception;
use Log;
use App\Http\Requests\ProductCreateRequest;
use App\Http\Requests\ProductEditRequest;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {

        $products = Product::where('is_deleted', false)->get();
        return response()->json([
            'error' => null,
            'data'  => $products
        ], 200);
    }

    /**
     * Create and Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ProductCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(ProductCreateRequest $request)
    {

        $product = Product::create($request->all());
        return response()->json([
            'error' => null,
            'data'  => [
                'id' => $product->id
            ]
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProduct($id)
    {

        $product = Product::find($id);
        if ($product != null && !$product->is_deleted) {
            return response()->json([
                'error' => null,
                'data' => [
                    'name' => $product->name,
                    'short_description' => $product->short_description,
                    'long_description' => $product->long_description,
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'sale_expire' => $product->sale_expire,
                    'video_props' => $product->video_props,
                    'category_ones_id' => $product->category_ones_id,
                    'category_twos_id' => $product->category_twos_id,
                    'category_threes_id' => $product->category_threes_id,
                    'category_fours_id' => $product->category_fours_id,
                    'main_image_path' => $product->main_image_path,
                    'main_image_thumb_path' => $product->main_image_thumb_path,
                    'second_image_path' => $product->second_image_path,
                    'published' => $product->published,
                    'type' => $product->type
                ]
            ]);
        }
        return response()->json([
            'error' => 'Product not found!',
            'data'  =>  null
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\ProductEditRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id, ProductEditRequest $request)
    {

        $product = Product::find($id);
        if($product != null && !$product->is_deleted){
            $product->update($request->all());
            return response()->json([
                'error' => null,
                'data'  => null
            ], 200);
        }
        return response()->json([
            'error' => 'Product not found!',
            'data' => null
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        if ($product != null) {
            $product->is_deleted = 1;
            try {
                $product->save();
                return response()->json([
                    'error' => null,
                    'data'  =>  null
                ], 200);
            } catch (Exception $e) {
                //Log::info('fail in ProductController/destroy'. json_encode($e));
                return response()->json([
                    'error' => 'fail in ProductController/destroy'. json_encode($e),
                    'data'  =>  null
                ], 500);
            }
        }
        return response()->json([
            'error' => 'Product not found!',
            'data'  =>  null
        ], 404);
    }
}
