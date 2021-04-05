<?php

namespace App\Http\Controllers;

use Exception;
use Log;
use App\Http\Requests\ProductCreateRequest;
use App\Http\Requests\ProductEditRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Utils\UploadImage;


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
        return (new ProductCollection($products))->additional([
            'error' => null
        ])->response()->setStatusCode(200);
    }

    /**
     * Create and Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ProductCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    //<img src="{{ Storage::disk('public')->url($model->path) }}">
    public function create(ProductCreateRequest $request)
    {

        $product = Product::create($request->except(['main_image_path','main_image_thumb_path','second_image_path']));
        $upload_image = new UploadImage;
        $product->main_image_path = $upload_image->getImage($request->file('main_image_path'),"main");
        $product->second_image_path = $upload_image->getImage($request->file('second_image_path'),"second");
        $product->save();
        return (new ProductResource($product))->additional([
            'error' => null
        ])->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProduct($id)
    {

        $product = Product::where('is_deleted',false)->find($id);
        if ($product != null) {
            return (new ProductResource($product))->additional([
                'error' => null
            ])->response()->setStatusCode(200);
        }
        return (new ProductResource($product))->additional([
            'error' => 'Product not found!'
        ])->response()->setStatusCode(404);
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

        $product = Product::where('is_deleted',false)->find($id);
        if($product != null){
            $product->update($request->all());
            return (new ProductResource(null))->additional([
                'error' => null
            ])->response()->setStatusCode(200);
        }
        return (new ProductResource(null))->additional([
            'error' => 'Product not found!',
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
        $product = Product::find($id);
        if ($product != null) {
            $product->is_deleted = 1;
            try {
                $product->save();
                return (new ProductResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('fail in ProductController/destroy'. json_encode($e));
                return (new ProductResource(null))->additional([
                    'error' => 'fail in ProductController/destroy'. json_encode($e),
                ])->response()->setStatusCode(500);
            }
        }
        return (new ProductResource(null))->additional([
            'error' => 'Product not found!',
        ])->response()->setStatusCode(404);
    }
}
