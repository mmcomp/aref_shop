<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignVideoToProductRequest;
use App\Http\Requests\ProductDetailVideoIndexRequest;
use App\Http\Requests\ProductDetailVideosCreateRequest;
use App\Http\Requests\ProductDetailVideosEditRequest;
use App\Http\Resources\ProductDetailVideosCollection;
use App\Http\Resources\ProductDetailVideosResource;
use App\Models\ProductDetailVideo;
use App\Models\UserVideoSession;
use App\Utils\RaiseError;
use App\Utils\UpdatePreviousByers;
use Exception;
use Log;

class ProductDetailVideosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Requests\ProductDetailVideoIndexRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ProductDetailVideoIndexRequest $request)
    {

        $sort = "id";
        $sort_dir = "desc";
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sort_dir = $request->get('sort_dir');
        }
        if ($request->get('per_page') == "all") {
            $product_detail_videos = ProductDetailVideo::where('is_deleted', false)->orderBy($sort, $sort_dir)->get();
        } else {
            $product_detail_videos = ProductDetailVideo::where('is_deleted', false)->orderBy($sort, $sort_dir)->paginate(env('PAGE_COUNT'));
        }
        return (new ProductDetailVideosCollection($product_detail_videos))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ProductDetailVideosCreateRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductDetailVideosCreateRequest $request)
    {

        $found_product_detail_video = ProductDetailVideo::where('is_deleted', false)->where('products_id', $request->input('products_id'))->where('video_sessions_id', $request->input('video_sessions_id'))->first();
        $updatePreviousBuyers = new UpdatePreviousByers;
        $output = $updatePreviousBuyers->create($found_product_detail_video, $request);
        return (new ProductDetailVideosResource($output))->additional([
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

        $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($id);
        if ($product_detail_video != null) {
            return (new ProductDetailVideosResource($product_detail_video))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductDetailVideosResource($product_detail_video))->additional([
            'errors' => ['product_detail_video' => ['ProductDetailVideo not found!']],
        ])->response()->setStatusCode(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\ProductDetailVideosEditRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ProductDetailVideosEditRequest $request, $id)
    {

        $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($id);
        $updatePreviousBuyers = new UpdatePreviousByers;
        $updatePreviousBuyers->update($request, $product_detail_video);
        if ($product_detail_video == null) {
            return (new ProductDetailVideosResource(null))->additional([
                'errors' => ['product_detail_video' => ['ProductDetailVideo not found!']],
            ])->response()->setStatusCode(404);
        }
        return (new ProductDetailVideosResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {

        $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($id);
        if ($product_detail_video != null) {
            $product_detail_video->is_deleted = 1;
            try {
                $product_detail_video->save();
                UserVideoSession::where('video_sessions_id', $product_detail_video->video_sessions_id)->delete();
                return (new ProductDetailVideosResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in ProductDetailVideosController/destory', json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductDetailVideosResource(null))->additional([
                        'errors' => ['fail' => ['productDetailVideos deleting failed!' . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductDetailVideosResource(null))->additional([
                        'errors' => ['fail' => ['productDetailVideos deleting failed!']],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new ProductDetailVideosResource(null))->additional([
            'errors' => ['product_detail_video' => ['ProductDetailVideo not found!']],
        ])->response()->setStatusCode(404);
    }
    /**
     * assings a video to a product
     *
     * @param  \App\Http\Requests\AssignVideoToProductRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignVideoToProduct(AssignVideoToProductRequest $request)
    {

        $raiseError = new RaiseError;
        $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($request->input('product_detail_videos_id'));
        $foundProductDetailVideoWithThatVideoSession = ProductDetailVideo::where('is_deleted', false)->where('products_id', $request->input('products_id'))->where('video_sessions_id', $product_detail_video->video_sessions_id)->first();
        $lastProductDetailVideoOfTheRequestedProduct = ProductDetailVideo::join('video_sessions', 'video_sessions.id', '=', 'product_detail_videos.video_sessions_id')
            ->where('product_detail_videos.is_deleted', false)
            ->where('video_sessions.is_deleted', false)
            ->where('products_id', $request->input('products_id'))
            ->orderBy('video_sessions.start_date', 'desc')->first();
        $raiseError->ValidationError($product_detail_video->products_id == $request->input('products_id'), ['products_id' => ['Please enter a new product!']]);
        $raiseError->ValidationError($foundProductDetailVideoWithThatVideoSession, ['video_sessions_id' => ['The session is already saved!']]);
        if ($product_detail_video->videoSession && $lastProductDetailVideoOfTheRequestedProduct) {
            $raiseError->ValidationError(($lastProductDetailVideoOfTheRequestedProduct->start_date > $product_detail_video->videoSession->start_date && !$request->input('extraordinary')), ['extraordinary' => ['The extraordinary field should be 1']]);
        }
        ProductDetailVideo::create([
            'products_id' => $request->input('products_id'),
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'extraordinary' => $request->input('extraordinary'),
            'is_hidden' => $request->input('is_hidden') ? $request->input('is_hidden') : 0,
            'single_purchase' => $request->input('single_purchase'),
            'video_sessions_id' => $product_detail_video->videoSession ? $product_detail_video->video_sessions_id :  $raiseError->ValidationError(!$product_detail_video->videoSession, ['video_sessions_id' => ['The product_detail_videos videoSession is not valid!']])
        ]);
        return (new ProductDetailVideosResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }
}
