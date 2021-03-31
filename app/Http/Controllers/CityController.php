<?php

namespace App\Http\Controllers;

use App\Http\Requests\CityCreateRequest;
use App\Http\Requests\CityUpdateRequest;
use App\Models\City;
use Exception;
use Log;

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
            'error' => null,
            'data' => $cities,
        ], 200);
    }

    /**
     * Get city and return its properties
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCity($id)
    {

        $city = City::find($id);
        if ($city != null && !$city->is_deleted) {
            return response()->json([
                'error' => null,
                'data'  => [
                    'name' => $city->name,
                    'provinces_id' => $city->provinces_id,
                    'created_at' => $city->created_at,
                    'updated_at' => $city->updated_at
                ]
            ], 200);
        }
        return response()->json([
            'error' => 'City not found!',
            'data'  => null
        ], 404);
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
        return response()->json([
            'error' => null,
            'data'  => $city->id
        ], 201);
    }

    /**
     * Edit and Update the specified resource in storage.
     *
     * @param  App\Http\Requests\CityUpdateRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(CityUpdateRequest $request, $id)
    {

        $city = City::find($id);
        if ($city != null && !$city->is_deleted) {
            $city->update($request->all());
            return response()->json([
                'error' => null,
                'data'  => null
            ], 200);
        }
        return response()->json([
            'error' => 'City not found!',
            'data' => null
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $city = City::find($id);
        if ($city != null) {
            $city->is_deleted = 1;
            try {
                $city->save();
                return response()->json([
                    'error' => null,
                    'data' => null
                ], 200);
            } catch (Exception $e) {
                Log::info('failed in CityController/destory', json_encode($e));
                return response()->json([
                    'error' => 'failed in CityController/destory',
                    'data' => null
                ], 500);
            }
        }
        return response()->json([
            'error' => 'City not found!',
            'data' => null
        ], 404);
    }
}
