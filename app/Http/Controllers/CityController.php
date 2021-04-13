<?php

namespace App\Http\Controllers;

use App\Http\Requests\CityCreateRequest;
use App\Http\Requests\CityUpdateRequest;
use App\Http\Resources\CityCollection;
use App\Http\Resources\CityResource;
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

        $per_page = request()->get('per_page');
        if ($per_page == "all") {
            $cities = City::where('is_deleted', false)->orderBy('id', 'desc')->get();
        } else {
            $cities = City::where('is_deleted', false)->orderBy('id', 'desc')->paginate(env('PAGE_COUNT'));
        }
        return (new CityCollection($cities))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Get city and return its properties
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCity($id)
    {

        $city = City::where('is_deleted', false)->find($id);
        if ($city != null) {
            return (new CityResource($city))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new CityResource($city))->additional([
            'error' => 'City not found!',
        ])->response()->setStatusCode(404);

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
            'provinces_id' => $request->provinces_id,
        ]);
        return (new CityResource($city))->additional([
            'error' => null
        ])->response()->setStatusCode(201);
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

        $city = City::where('is_deleted',false)->find($id);
        if ($city != null) {
            $city->update($request->all());
            return (new CityResource(null))->additional([
                'error' => null
            ])->response()->setStatusCode(200);
        }
        return (new CityResource(null))->additional([
            'error' => 'City not found!',
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

        $city = City::where('is_deleted',false)->find($id);
        if ($city != null) {
            $city->is_deleted = 1;
            try {
                $city->save();
                return (new CityResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in CityController/destory', json_encode($e));
                return (new CityResource(null))->additional([
                    'error' => 'City deleting failed!',
                ])->response()->setStatusCode(500);
            }
        }
        return (new CityResource(null))->additional([
            'error' => 'City not found!',
        ])->response()->setStatusCode(404);
    }
}
