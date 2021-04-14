<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductDetailVideosCreateRequest;
use App\Http\Requests\ProductDetailVideosEditRequest;
use App\Http\Resources\ProductDetailVideosCollection;
use App\Http\Resources\ProductDetailVideosResource;
use App\Models\ProductDetailVideo;
use Exception;
use Log;

class ProductDetailVideosController extends Controller
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
            $product_detail_videos = ProductDetailVideo::where('is_deleted', false)->orderBy('id', 'desc')->get();
        } else {
            $product_detail_videos = ProductDetailVideo::where('is_deleted', false)->orderBy('id', 'desc')->paginate(env('PAGE_COUNT'));
        }
        return (new ProductDetailVideosCollection($product_detail_videos))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ProductDetailVideosCreateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductDetailVideosCreateRequest $request)
    {

        $product_detail_video = ProductDetailVideo::create($request->all());
        return (new ProductDetailVideosResource($product_detail_video))->additional([
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

        $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($id);
        if ($product_detail_video != null) {
            return (new ProductDetailVideosResource($product_detail_video))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductDetailVideosResource($product_detail_video))->additional([
            'error' => 'ProductDetailVideo not found!',
        ])->response()->setStatusCode(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\ProductDetailVideosEditRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductDetailVideosEditRequest $request, $id)
    {

        $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($id);
        if ($product_detail_video != null) {
            $product_detail_video->update($request->all());
            return (new ProductDetailVideosResource(null))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductDetailVideosResource(null))->additional([
            'error' => 'ProductDetailVideo not found!',
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

        $product_detail_video = ProductDetailVideo::where('is_deleted',false)->find($id);
        if ($product_detail_video != null) {
            $product_detail_video->is_deleted = 1;
            try {
                $product_detail_video->save();
                return (new ProductDetailVideosResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in ProductDetailVideosController/destory', json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductDetailVideosResource(null))->additional([
                        'error' => 'productDetailVideos deleting failed!' . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductDetailVideosResource(null))->additional([
                        'error' => 'productDetailVideos deleting failed!',
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new ProductDetailVideosResource(null))->additional([
            'error' => 'ProductDetailVideo not found!',
        ])->response()->setStatusCode(404);
    }
}