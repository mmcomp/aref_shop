<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupGateCreateRequest;
use App\Http\Requests\GroupGateEditRequest;
use App\Models\GroupGate;
use App\Http\Resources\GroupGateCollection;
use App\Http\Resources\GroupGateResource;
use Illuminate\Support\Facades\Auth;
use Exception;
use Log;

class GroupGatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        
        $group_gates = GroupGate::where('is_deleted',false)->get();
        return (new GroupGateCollection($group_gates))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\GroupGateCreateRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(GroupGateCreateRequest $request)
    {

        $group_gate = GroupGate::create([
            'key' => $request->key,
            'groups_id' => $request->groups_id,
            'users_id' => Auth::user()->id
        ]);
        return (new GroupGateResource($group_gate))->additional([
            'errors' => null
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

        $group_gate = GroupGate::where('is_deleted',false)->find($id);
        if ($group_gate != null) {
            return (new GroupGateResource($group_gate))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new GroupGateResource($group_gate))->additional([
            'errors' => ['group_gate' => ['GroupGate not found!']],
        ])->response()->setStatusCode(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\GroupGateEditRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(GroupGateEditRequest $request, $id)
    {
        
        $group_gate = GroupGate::where('is_deleted',false)->find($id);
        if ($group_gate != null) {
            $group_gate->users_id = Auth::user()->id;
            $group_gate->update($request->all());
            return (new GroupGateResource(null))->additional([
                'errors' => null
            ])->response()->setStatusCode(200);
        }
        return (new GroupGateResource(null))->additional([
            'errors' => ['group_gate' => ['GroupGate not found!']],
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
        
        $group_gate = GroupGate::find($id);
        if ($group_gate != null) {
            $group_gate->is_deleted = 1;
            try {
                $group_gate->save();
                return (new GroupGateResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in GroupGatesController/destory', json_encode($e));
                if (env('APP_ENV') == "development") {
                    return (new GroupGateResource(null))->additional([
                        'errors' => ['fail' => ['GroupGates deleting failed!'. json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } elseif (env('APP_ENV') == "production") {
                    return (new GroupGateResource(null))->additional([
                        'errors' => ['fail' => ['GroupGates deleting failed!']],
                    ])->response()->setStatusCode(500);
                }
               
            }
        }
        return (new GroupGateResource(null))->additional([
            'errors' => ['group_gate' => ['GroupGate not found!']],
        ])->response()->setStatusCode(404);
    }
}
