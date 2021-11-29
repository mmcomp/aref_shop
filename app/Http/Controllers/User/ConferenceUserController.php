<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ConferenceUser;
use Illuminate\Http\Request;
use App\Http\Requests\User\ConferenceUserRequest;
use App\Http\Resources\User\ConferenceUserResource;


class ConferenceUserController extends Controller
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
     * @param  App\Http\Requests\User\ConferenceUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(ConferenceUserRequest $request)
    {
        $conferenceUser = ConferenceUser::create([
            'product_detail_videos_id' => $request->product_detail_videos_id,
            'users_id' => $request->users_id,
            'referrer' => $request->referrer,
            'already_registerd' => $request->already_registerd
        ]);
        return (new ConferenceUserResource($conferenceUser))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ConferenceUser  $conferenceUser
     * @return \Illuminate\Http\Response
     */
    public function show(ConferenceUser $conferenceUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ConferenceUser  $conferenceUser
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ConferenceUser $conferenceUser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ConferenceUser  $conferenceUser
     * @return \Illuminate\Http\Response
     */
    public function destroy(ConferenceUser $conferenceUser)
    {
        //
    }
}
