<?php

namespace App\Http\Controllers;

use App\Http\Requests\CouponCreateRequest;
use App\Http\Requests\CouponEditRequest;
use App\Http\Requests\CouponIndexRequest;
use App\Models\Coupon;
use App\Http\Resources\CouponCollection;
use App\Http\Resources\CouponResource;
use Exception;
use Log;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Requests\CouponIndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(CouponIndexRequest $request)
    {
        
        $sort = "id";
        $sort_dir = "desc";
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sort_dir = $request->get('sort_dir');
        }
        if ($request->get('per_page') == "all") {
            $coupons = Coupon::where('is_deleted', false)->orderBy($sort, $sort_dir)->get();

        } else {
            $coupons = Coupon::where('is_deleted', false)->orderBy($sort, $sort_dir)->paginate(env('PAGE_COUNT'));
        }
        return (new CouponCollection($coupons))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CouponCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CouponCreateRequest $request)
    {
        
        $coupon = Coupon::create($request->all());
        return (new CouponResource($coupon))->additional([
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
        
        $coupon = Coupon::where('is_deleted', false)->find($id);
        if ($coupon != null) {
            return (new CouponResource($coupon))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new CouponResource($coupon))->additional([
            'errors' => ['coupon' => ['Coupon not found!']],
        ])->response()->setStatusCode(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\CouponEditRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CouponEditRequest $request, $id)
    {
        
        $coupon = Coupon::where('is_deleted', false)->find($id);
        if ($coupon != null) {
            $coupon->update($request->all());
            return (new CouponResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new CouponResource(null))->additional([
            'errors' => ['coupon' => ['Coupon not found!']],
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
        
        $coupon = Coupon::where('is_deleted', false)->find($id);
        if ($coupon != null) {
            $coupon->is_deleted = 1;
            try {
                $coupon->save();
                return (new CouponResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in CouponController/destory', json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new CouponResource(null))->additional([
                        'errors' => ['fail' => ['Coupon deleting failed! ' . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new CouponResource(null))->additional([
                        'errors' => ['fail' => ['Coupon deleting failed!']],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new CouponResource(null))->additional([
            'errors' => ['coupon' => ['Coupon not found!']],
        ])->response()->setStatusCode(404);
    }
}
