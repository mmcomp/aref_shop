<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductDetailDownloadsCreateRequest;
use App\Http\Requests\ProductDetailDownloadsEditRequest;
use App\Http\Resources\ProductDetailDownloadsCollection;
use App\Http\Resources\ProductDetailDownloadsResource;
use App\Models\ProductDetailDownload;
use App\Utils\UploadImage;
use Exception;
use Log;

class ProductDetailDownloadsController extends Controller
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
            $product_detail_downloads = ProductDetailDownload::where('is_deleted', false)->orderBy('id', 'desc')->get();
        } else {
            $product_detail_downloads = ProductDetailDownload::where('is_deleted', false)->orderBy('id', 'desc')->paginate(env('PAGE_COUNT'));
        }
        return (new ProductDetailDownloadsCollection($product_detail_downloads))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ProductDetailDownloadsCreateRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductDetailDownloadsCreateRequest $request)
    {

        $product_detail_downloads = ProductDetailDownload::create($request->except('file_path'));
        $upload_image = new UploadImage;
        $product_detail_downloads->file_path = $upload_image->getImage($request->file('file_path'), "public/uploads/downloads");
        try {
            $product_detail_downloads->save();
        } catch (Exception $e) {
            Log::info("fails in saving file " . json_encode($e));
            if (env('APP_ENV') == 'development') {
                return (new ProductDetailDownloadsResource(null))->additional([
                    'error' => "fails in saving file" . json_encode($e),
                ])->response()->setStatusCode(500);
            } else if (env('APP_ENV') == 'production') {
                return (new ProductDetailDownloadsResource(null))->additional([
                    'error' => "fails in saving image",
                ])->response()->setStatusCode(500);
            }
        }
        return (new ProductDetailDownloadsResource($product_detail_downloads))->additional([
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

        $product_detail_download = ProductDetailDownload::where('is_deleted', false)->find($id);
        if ($product_detail_download != null) {
            return (new ProductDetailDownloadsResource($product_detail_download))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductDetailDownloadsResource($product_detail_download))->additional([
            'error' => 'ProductDetailDownload not found!',
        ])->response()->setStatusCode(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\ProductDetailDownloadsEditRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductDetailDownloadsEditRequest $request, $id)
    {

        $product_detail_download = ProductDetailDownload::where('is_deleted', false)->find($id);
        if ($product_detail_download != null) {
            $product_detail_download->update($request->all());
            return (new ProductDetailDownloadsResource(null))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductDetailDownloadsResource(null))->additional([
            'error' => 'ProductDetailDownloads not found!',
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

        $product_detail_download = ProductDetailDownload::where('is_deleted',false)->find($id);
        if ($product_detail_download != null) {
            $product_detail_download->is_deleted = 1;
            try {
                $product_detail_download->save();
                return (new ProductDetailDownloadsResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in ProductDetailDownloadsController/destory', json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductDetailDownloadsResource(null))->additional([
                        'error' => 'failed in ProductDetailDownloadsController/destory '.json_encode($e)
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductDetailDownloadsResource(null))->additional([
                        'error' => 'failed in ProductDetailDownloadsController/destory'
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new ProductDetailDownloadsResource(null))->additional([
            'error' => 'ProductDetailDownloads not found!',
        ])->response()->setStatusCode(404);
    }
}
