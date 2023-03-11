<?php

namespace App\Http\Controllers;

use App\Http\Requests\CouponCreateRequest;
use App\Http\Requests\CouponEditRequest;
use App\Http\Requests\CouponIndexRequest;
use App\Models\Coupon;
use App\Http\Resources\CouponCollection;

use App\Http\Resources\CouponResource;
use Exception;
use Illuminate\Support\Facades\DB;
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
        $name = null;
        if ($request->get('name') != null) {
            $name = $request->get('name');
        }

        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sort_dir = $request->get('sort_dir');
        }
        if ($request->get('per_page') == "all") {
            $coupons = Coupon::where('is_deleted', false)
            ->with('orderDetail.user')
            ->orderBy($sort, $sort_dir)           
            ->get();
        } else {
            if ($name != null) {
                $coupons = Coupon::where('is_deleted', false)->where('name', 'like', '%' . $name . '%')
                ->with('orderDetail.user')
                ->orderBy($sort, $sort_dir)
                ->paginate(env('PAGE_COUNT'));
            } else {
                $coupons = Coupon::where('is_deleted', false)
                ->with('orderDetail.user')
                ->orderBy($sort, $sort_dir)
                ->paginate(env('PAGE_COUNT'));
            }
        }
        
        return (new CouponCollection($coupons))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
    public function store(CouponCreateRequest $request)
    {        
        $name = $request->name;        
        $creation = array(
            "name" => $name,
            "amount" => $request->amount,
            "type" => $request->type,
            "products_id" => $request->products_id,
            "description" => $request->description
        );
        $creation;

        //dd($creation);

        //$coupon = DB::table('coupons')->insert($creationCollection);

        //$coupon = Coupon::create($request->all());
        $coupon = Coupon::create($creation);
        return (new CouponResource($coupon))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);

        // return (new CouponResource(null))->additional([
        //     'errors' => null,
        // ])->response()->setStatusCode(201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CouponCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function customized_store(CouponCreateRequest $request)
    {
        $count = isset($request->count) ? $request->count : 1;
        $creationCollection = array();

        for ($i = 1; $i <= $count; $i++) {
            $name = $this->createRandomCoupon($request->name);
            if (!$name) {
                continue;
            }
            $creation = array(

                "name" => $name,
                "amount" => $request->amount,
                "type" => $request->type,
                "products_id" => $request->products_id,
            );
            $creationCollection[] = $creation;
        }
        $coupon = DB::table('coupons')->insert($creationCollection);

        //$coupon = Coupon::create($request->all());
        // $coupon = Coupon::create($creationCollection);
        // return (new CouponResource($coupon))->additional([
        //     'errors' => null,
        // ])->response()->setStatusCode(201);

        return (new CouponResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }
    public function createRandomCoupon(string $prefix)
    {
        $i = 0;
        while (true) {
            $i++;
            if ($i == 100)
                return null;
            $name = $prefix . rand(10000, 99999);
            $count = Coupon::where('name', $name)->count();
            if ($count === 0) {
                return $name;
            }
        }
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
