<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductDetailChairsCreateRequest;
use App\Http\Requests\ProductDetailChairsEditRequest;
use App\Http\Resources\ProductChairsCollection;
use App\Http\Resources\ProductDetailChairsCollection;
use App\Http\Resources\ProductDetailChairsResource;
use App\Models\ProductDetailChair;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Utils\AdminLog;
use App\Models\User;

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
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Create & Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\ProductDetailChairsCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductDetailChairsCreateRequest $request)
    {

        $records_between = ProductDetailChair::whereProductsId($request->products_id)
            ->where('start', '>=', $request->start)
            ->where('end', '<=', $request->end)
            ->count();
        if ($records_between > 0) {
            return (new ProductDetailChairsResource(null))->additional([
                'errors' => [
                    'rangeNumber' => [
                        'chairs number already registerd'
                    ]
                ]
            ])->response()->setStatusCode(422);
        }

        $product_detail_chair = ProductDetailChair::create($request->all());
        return (new ProductDetailChairsResource($product_detail_chair))->additional([
            'errors' => null
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
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProductDetailChairsResource($product_detail_chair))->additional([
            'errors' => ['product_detail_chair' => ['ProductDetailChair not found!']],
        ])->response()->setStatusCode(404);
    }

    /**
     * Edit & Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, ProductDetailChairsEditRequest $request)
    {
        $product_detail_chair = ProductDetailChair::where('is_deleted', false)->find($id);
        if ($product_detail_chair != null) {
            $product_detail_chair->update($request->all());
            return (new ProductDetailChairsResource(null))->additional([
                'errors' => null
            ])->response()->setStatusCode(200);
        }
        return (new ProductDetailChairsResource(null))->additional([
            'errors' => ['product_detail_chair' => ['ProductDetailChair not found!']],
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
        $product_detail_chair = ProductDetailChair::where('is_deleted', false)->find($id);
        if ($product_detail_chair != null) {
            try {   
                $user_id=Auth::user()->id;
                $adminLog= new AdminLog;
                $product_detail_chair_tmp= $product_detail_chair->getTable()  .  $product_detail_chair;             
                $response=$adminLog->addLog($user_id,(string)$product_detail_chair_tmp,"delete");                          
                $product_detail_chair->delete();
               
                return (new ProductDetailChairsResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {               
            Log::info('failed in ProductDetailChairsController/destory'.json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductDetailChairsResource(null))->additional([
                        'errors' => ["fail" => ['failed in ProductDetailChairsController/destory ' . json_encode($e)]]
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductDetailChairsResource(null))->additional([
                        'errors' => ["fail" => ['failed in ProductDetailChairsController/destory']]
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new ProductDetailChairsResource(null))->additional([
            'errors' => ['product_detail_chair' => ['ProductDetailChair not found!']],
        ])->response()->setStatusCode(404);
    }

    /**
     * Display a listing of the product chairs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function productIndex(int $product_id)
    {
        $per_page = request()->get('per_page');
        $product_detail_chairs = ProductDetailChair::where('is_deleted', false)
            ->whereProductsId($product_id)
            ->orderBy('start', 'asc');
        if ($per_page == "all") {
            $product_detail_chairs = $product_detail_chairs->get();
        } else {
            $product_detail_chairs = $product_detail_chairs->paginate(env('PAGE_COUNT'));
        }
        return (new ProductChairsCollection($product_detail_chairs))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
    // public function getDetails($user_id,$before,$after)
    // {
    //    $audit=new AdminLog;
    //    $user=User::whereId($user_id)->first();
    //   
    //    $user_fullName=$user->first_name . " " . $user->last_name;
    //   
    //    $log_result=AdminLog::deleteRecord($user->id,$user_fullName,$before,$after);
    //     return $log_result;

    // }
}
