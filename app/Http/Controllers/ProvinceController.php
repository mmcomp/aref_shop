<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProvinceCreateRequest;
use App\Http\Requests\ProvinceEditRequest;
use App\Http\Requests\ProvinceIndexRequest;
use App\Http\Resources\ProvinceCollection;
use App\Http\Resources\ProvinceResource;
use App\Models\Province;
use Exception;
use Log;

class ProvinceController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param  App\Http\Requests\ProvinceIndexRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(ProvinceIndexRequest $request)
    {

        $sort = "id";
        $type = "desc";
        if ($request->get('type') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $type = $request->get('type');
        }
        if ($request->get('per_page') == "all") {
            $provinces = Province::where('is_deleted', false)->orderBy($sort, $type)->get();
        } else {
            $provinces = Province::where('is_deleted', false)->orderBy($sort, $type)->paginate(env('PAGE_COUNT'));
        }
        return (new ProvinceCollection($provinces))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }
    /**
     * get cities of a province(input = province_id)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCitiesOfAProvince($id)
    {

        $province = Province::where('is_deleted', false)->find($id);
        if ($province != null) {
            return (new ProvinceCollection($province->cities))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProvinceResource($province))->additional([
            'error' => "Province not found!",
        ])->response()->setStatusCode(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\ProvinceCreateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProvinceCreateRequest $request)
    {

        $province = Province::create([
            'name' => $request->name,
        ]);
        return (new ProvinceResource($province))->additional([
            'error' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $province = Province::where('is_deleted', false)->find($id);
        if ($province != null) {
            return (new ProvinceResource($province))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProvinceResource($province))->additional([
            'error' => 'Province not found!',
        ])->response()->setStatusCode(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\ProvinceEditRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProvinceEditRequest $request, $id)
    {

        $province = Province::where('is_deleted', false)->find($id);
        if ($province != null) {
            $province->update($request->all());
            return (new ProvinceResource(null))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new ProvinceResource(null))->additional([
            'error' => 'Province not found!',
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

        $province = Province::where('is_deleted', false)->find($id);
        if ($province != null) {
            $province->is_deleted = 1;
            try {
                $province->save();
                return (new ProvinceResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in ProvinceController/destory', json_encode($e));
                return (new ProvinceResource(null))->additional([
                    'error' => 'Province deleting failed!',
                ])->response()->setStatusCode(500);
            }
        }
        return (new ProvinceResource(null))->additional([
            'error' => 'Province not found!',
        ])->response()->setStatusCode(404);
    }
}
