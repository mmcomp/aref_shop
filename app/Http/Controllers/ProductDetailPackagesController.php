<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductDetailPackagesCreateRequest;
use App\Http\Requests\ProductDetailPackagesEditRequest;
use App\Http\Resources\ProductDetailPackagesCollection;
use App\Http\Resources\ProductDetailPackagesResource;
use App\Models\ProductDetailPackage;
use Exception;
use Log;

class ProductDetailPackagesController extends Controller
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
            $product_detail_packages = ProductDetailPackage::where('is_deleted', false)->orderBy('id', 'desc')->get();
        } else {
            $product_detail_packages = ProductDetailPackage::where('is_deleted', false)->orderBy('id', 'desc')->paginate(env('PAGE_COUNT'));
        }
        return (new ProductDetailPackagesCollection($product_detail_packages))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\ProductDetailPackagesCreateRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductDetailPackagesCreateRequest $request)
    {

        $product_detail_package = ProductDetailPackage::create($request->all());
        return (new ProductDetailPackagesResource($product_detail_package))->additional([
            'error' => null,
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

        $product_detail_package = ProductDetailPackage::where('is_deleted', false)->find($id);
        if ($product_detail_package != null) {
            return (new ProductDetailPackagesResource($product_detail_package))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductDetailPackagesResource($product_detail_package))->additional([
            'error' => 'ProductDetailPackage not found!',
        ])->response()->setStatusCode(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\ProductDetailPackagesEditRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ProductDetailPackagesEditRequest $request, $id)
    {

        $product_detail_package = ProductDetailPackage::where('is_deleted', false)->find($id);
        if ($product_detail_package != null) {
            $product_detail_package->update($request->all());
            return (new ProductDetailPackagesResource(null))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductDetailPackagesResource(null))->additional([
            'error' => 'ProductDetailPackages not found!',
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

        $product_detail_package = ProductDetailPackage::where('is_deleted', false)->find($id);
        if ($product_detail_package != null) {
            $product_detail_package->is_deleted = 1;
            try {
                $product_detail_package->save();
                return (new ProductDetailPackagesResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in ProductDetailPackagesController/destory', json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductDetailPackagesResource(null))->additional([
                        'error' => 'failed in ProductDetailPackagesController/destory ' . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductDetailPackagesResource(null))->additional([
                        'error' => 'failed in ProductDetailPackagesController/destory',
                    ])->response()->setStatusCode(500);
                }

            }
        }
        return (new ProductDetailPackagesResource(null))->additional([
            'error' => 'ProductDetailPackages not found!',
        ])->response()->setStatusCode(404);
    }
}
