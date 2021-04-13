<?php

namespace App\Http\Controllers;

use App\Http\Requests\CityCreateRequest;
use App\Http\Requests\CityIndexRequest;
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
     * @param  \App\Http\Requests\CityIndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(CityIndexRequest $request)
    {
        $sort = "id";
        $type = "desc";
        if ($request->get('type') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $type = $request->get('type');
        }
        if ($request->get('per_page') == "all") {
            $cities = City::where('is_deleted', false)->orderBy($sort, $type)->get();

        } else {
            $cities = City::where('is_deleted', false)->orderBy($sort, $type)->paginate(env('PAGE_COUNT'));
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
    public function show($id)
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
    public function store(CityCreateRequest $request)
    {

        $city = City::create([
            'name' => $request->name,
            'provinces_id' => $request->provinces_id,
        ]);
        return (new CityResource($city))->additional([
            'error' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * Edit and Update the specified resource in storage.
     *
     * @param  App\Http\Requests\CityUpdateRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CityUpdateRequest $request, $id)
    {

        $city = City::where('is_deleted', false)->find($id);
        if ($city != null) {
            $city->update($request->all());
            return (new CityResource(null))->additional([
                'error' => null,
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

        $city = City::where('is_deleted', false)->find($id);
        if ($city != null) {
            $city->is_deleted = 1;
            try {
                $city->save();
                return (new CityResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in CityController/destory', json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new CityResource(null))->additional([
                        'error' => 'City deleting failed! ' . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new CityResource(null))->additional([
                        'error' => 'City deleting failed!',
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new CityResource(null))->additional([
            'error' => 'City not found!',
        ])->response()->setStatusCode(404);
    }
}
