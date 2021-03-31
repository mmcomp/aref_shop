<?php

namespace App\Http\Controllers;

use App\Http\Requests\CityCreateRequest;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {

        $cities = City::where('is_deleted', false)->get();
        return response()->json([
            'cities' => $cities,
        ], 201);
    }

    /**
     * Get city and return its properties
     *
     * @param id $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCity($id)
    {

        $city = City::findOrFail($id);
        if(!$city->is_deleted){
            return response()->json([
                'name' => $city->name,
                'provinces_id' => $city->provinces_id,
                'created_at' => $city->created_at,
                'updated_at' => $city->updated_at
            ], 201);
        }
        return response()->json([
            'message' => 'The city you want is deleted!'
        ]);

    }

    /**
     * Create and Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\CityCreateRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(CityCreateRequest $request)
    {

        $city = City::create([
           'name' => $request->name,
           'provinces_id' => $request->provinces_id
        ]);
    }

    /**
     * Edit and Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
