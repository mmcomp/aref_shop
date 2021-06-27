<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserDescriptionCreateRequest;
use App\Models\UserDescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDescriptionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\UserDescriptionCreateRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserDescriptionCreateRequest $request)
    {
        
        $user_video_session_homeworks_id = $request->input('user_video_session_homeworks_id');
        $description = $request->input('description');
        $users_id = Auth::user()->id;
        UserDescription::create([
           'user_video_session_homeworks_id' => $user_video_session_homeworks_id,
           'description' => $description,
           'users_id' => $users_id
        ]);
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
