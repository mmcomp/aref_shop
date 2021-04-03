<?php

namespace App\Http\Controllers;

use App\Models\ProductDetailChair;
use Illuminate\Http\Request;

class ProductDetailChairsController extends Controller
{
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {

        $product_detail_chairs = ProductDetailChair::where('is_deleted',false)->get();
        return response()->json([
            'error' => null,
            'data'  => $product_detail_chairs
        ], 200);
    }

    /**
     * Create & Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $product_detail_chair = ProductDetailChair::create($request->all());
        return response()->json([
            'error' => null,
            'data'  => [
                'id' => $product_detail_chair->id
            ]
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
