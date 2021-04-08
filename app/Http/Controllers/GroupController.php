<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupCreateRequest;
use App\Http\Requests\GroupEditRequest;
use App\Http\Resources\GroupCollection;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use Exception;
use Log;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $groups = Group::where('is_deleted', false)->get();
        return (new GroupCollection($groups))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\GroupCreateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GroupCreateRequest $request)
    {

        $group = Group::create([
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description,
        ]);
        return (new GroupResource($group))->additional([
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

        $group = Group::where('is_deleted', false)->find($id);
        if ($group != null) {
            return (new GroupResource($group))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new GroupResource($group))->additional([
            'error' => 'Group not found!',
        ])->response()->setStatusCode(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\GroupEditRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(GroupEditRequest $request, $id)
    {

        $group = Group::where('is_deleted', false)->find($id);
        if ($group != null) {
            $group->update($request->all());
            return (new GroupResource(null))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new GroupResource(null))->additional([
            'error' => 'Group not found!',
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

        $group = Group::find($id);
        if ($group != null) {
            $group->is_deleted = 1;
            try {
                $group->save();
                return (new GroupResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in GroupController/destory', json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new GroupResource(null))->additional([
                        'error' => 'Group deleting failed!' . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new GroupResource(null))->additional([
                        'error' => 'Group deleting failed!',
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new GroupResource(null))->additional([
            'error' => 'Group not found!',
        ])->response()->setStatusCode(404);
    }
}
