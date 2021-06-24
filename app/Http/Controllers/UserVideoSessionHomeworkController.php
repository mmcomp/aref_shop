<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserVideoSessionHomeWorkCreateRequest;
use App\Http\Requests\UserVideoSessionHomeWorkIndexRequest;
use App\Http\Resources\UserVideoSessionHomeWorkCollection;
use App\Http\Resources\UserVideoSessionHomeWorkResource;
use App\Models\UserVideoSessionHomework;
use Illuminate\Http\Request;

class UserVideoSessionHomeworkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Requests\UserVideoSessionHomeWorkIndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(UserVideoSessionHomeWorkIndexRequest $request)
    {
        
        $per_page = $request->get('per_page');
        $user_video_session_homeworks = UserVideoSessionHomework::orderBy('id', 'desc');    
        if ($per_page == "all") {
            $user_video_session_homeworks = $user_video_session_homeworks->get();
        } else {
            $user_video_session_homeworks = $user_video_session_homeworks->paginate(env('PAGE_COUNT'));
        }
        return (new UserVideoSessionHomeWorkCollection($user_video_session_homeworks))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\UserVideoSessionHomeWorkCreateRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserVideoSessionHomeWorkCreateRequest $request)
    {
        
        $user_video_session_homework = UserVideoSessionHomework::create($request->all());
        return (new UserVideoSessionHomeWorkResource($user_video_session_homework))->additional([
            'errors' => null,
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
