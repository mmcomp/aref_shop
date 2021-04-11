<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductCreateRequest;
use App\Http\Requests\ProductEditRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Utils\UploadImage;
use Exception;
use Illuminate\Support\Facades\Storage;
use Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {

        $products = Product::where('is_deleted', false)->orderBy('id','desc')->paginate(env('PAGE_COUNT'));
        return (new ProductCollection($products))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Create and Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ProductCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(ProductCreateRequest $request)
    {

        $product = Product::create($request->except(['main_image_path', 'main_image_thumb_path', 'second_image_path']));
        $upload_image = new UploadImage;
        if($request->file('main_image_path')){
            $product->main_image_path = $upload_image->getImage($request->file('main_image_path'), "public/uploads", "main");
            $product->main_image_thumb_path = $upload_image->createThumbnail($request->file('main_image_path'));
        }
        if($request->file('second_image_path')){
            $product->second_image_path = $upload_image->getImage($request->file('second_image_path'), "public/uploads", "second");
        } 
        try {
            $product->save();
        } catch (Exception $e) {
            Log::info("fails in saving image " . json_encode($e));
            if (env('APP_ENV') == 'development') {
                return (new ProductResource(null))->additional([
                    'error' => "fails in saving image" . json_encode($e),
                ])->response()->setStatusCode(500);
            } else if (env('APP_ENV') == 'production') {
                return (new ProductResource(null))->additional([
                    'error' => "fails in saving image",
                ])->response()->setStatusCode(500);
            }

        }
        return (new ProductResource($product))->additional([
            'error' => null,
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

        $product = Product::where('is_deleted', false)->find($id);
        if ($product != null) {
            return (new ProductResource($product))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductResource($product))->additional([
            'error' => 'Product not found!',
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

        $product = Product::where('is_deleted', false)->find($id);
        if ($product != null) {
            $upload_image = new UploadImage;
            if ($product->main_image_path != null) {
                $main_image_path = str_replace("storage", "public", $product->main_image_path);
                $main_image_thumb_path = str_replace("storage", "public", $product->main_image_thumb_path);
                if (Storage::exists($main_image_path)) {
                    Storage::delete($main_image_path);
                    Storage::delete($main_image_thumb_path);
                }
            }
            if ($request->file('main_image_path')) {
                $product->main_image_path = $upload_image->getImage($request->file('main_image_path'), "public/uploads", "main");
                $product->main_image_thumb_path = $upload_image->createThumbnail($request->file('main_image_path'));
            } else {
                $product->main_image_path = null;
                $product->main_image_thumb_path = null;
            }
            if ($product->second_image_path != null) {
                $second_image_path = str_replace("storage", "public", $product->second_image_path);
                if (Storage::exists($second_image_path)) {
                    Storage::delete($second_image_path);
                }
            }
            if ($request->file('second_image_path')) {
                $product->second_image_path = $upload_image->getImage($request->file('second_image_path'), "public/uploads", "second");
            } else {
                $product->second_image_path = null;
            }
            $product->update($request->except(['main_image_path', 'main_image_thumb_path', 'second_image_path']));
            return (new ProductResource(null))->additional([
                'error' => null,
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
            if ($product->main_image_path != null) {
                $main_image_path = str_replace("storage", "public", $product->main_image_path);
                $main_image_thumb_path = str_replace("storage", "public", $product->main_image_thumb_path);
                if (Storage::exists($main_image_path)) {
                    Storage::delete($main_image_path);
                    Storage::delete($main_image_thumb_path);
                    $product->main_image_path = null;
                    $product->main_image_thumb_path = null;
                }
            }
            if ($product->second_image_path != null) {
                $second_image_path = str_replace("storage", "public", $product->second_image_path);
                if (Storage::exists($second_image_path)) {
                    Storage::delete($second_image_path);
                    $product->second_image_path = null;
                }
            }
            try {
                $product->save();
                return (new ProductResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('fail in ProductController/destroy' . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductResource(null))->additional([
                        'error' => 'fail in ProductController/destroy',
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductResource(null))->additional([
                        'error' => 'fail in ProductController/destroy',
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new ProductResource(null))->additional([
            'error' => 'Product not found!',
        ])->response()->setStatusCode(404);
    }
}
