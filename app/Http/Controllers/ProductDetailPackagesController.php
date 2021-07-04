<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductDetailPackagesCreateRequest;
use App\Http\Requests\ProductDetailPackagesEditRequest;
use App\Http\Resources\ProductDetailPackagesCollection;
use App\Http\Resources\ProductDetailPackagesResource;
use App\Models\ProductDetailPackage;
use App\Models\OrderDetail;
use App\Utils\RaiseError;
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
            'errors' => null,
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

        $raiseError = new RaiseError;
        $found_product_detail_package = ProductDetailPackage::where("products_id", $request->input("products_id"))->where('child_products_id', $request->input('child_products_id'))->where('is_deleted', false)->first();
        $raiseError->ValidationError($found_product_detail_package, ['repeated' => ['The productDetailPackage is repeated!']]);
        $product_detail_package = ProductDetailPackage::create($request->all());
        return (new ProductDetailPackagesResource($product_detail_package))->additional([
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

        $product_detail_package = ProductDetailPackage::where('is_deleted', false)->find($id);
        if ($product_detail_package != null) {
            return (new ProductDetailPackagesResource($product_detail_package))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductDetailPackagesResource($product_detail_package))->additional([
            'errors' => ['product_detail_package' => ['ProductDetailPackage not found!']],
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
        $raiseError = new RaiseError;
        if ($product_detail_package != null) {
            $products_id = $product_detail_package->products_id;
            $orderDetails = OrderDetail::get();
            foreach($orderDetails as $orderDetail) {
                if($orderDetail->order->status == "ok" && $orderDetail->product->type == "package") {
                   $ids[] = $orderDetail->product->id; 
                }
            }
            $raiseError->ValidationError(in_array($products_id, $ids), ['product_detail_package' => ['You should not change this relation!']]);
            if(!in_array($products_id, $ids)){
                $product_detail_package->update($request->all());
            } 

            return (new ProductDetailPackagesResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductDetailPackagesResource(null))->additional([
            'errors' => ['product_detail_package' => ['ProductDetailPackage not found!']],
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
                    'errors' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in ProductDetailPackagesController/destory', json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductDetailPackagesResource(null))->additional([
                        'errors' => ['fail' => ['failed in ProductDetailPackagesController/destory ' . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductDetailPackagesResource(null))->additional([
                        'errors' => ['fail' => ['failed in ProductDetailPackagesController/destory']],
                    ])->response()->setStatusCode(500);
                }

            }
        }
        return (new ProductDetailPackagesResource(null))->additional([
            'errors' => ['product_detail_package' => ['ProductDetailPackage not found!']],
        ])->response()->setStatusCode(404);
    }
}
