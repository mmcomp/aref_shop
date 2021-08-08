<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Http\Resources\User\NotificationResource;
use App\Http\Resources\User\NotificationCollection;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $notifications = Notification::where('is_deleted', false)->where('published', 1)->orderBy('date', 'desc')->get();
        return (new NotificationCollection($notifications))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $notification = Notification::where('is_deleted', false)->where('published', 1)->find($id);
        if ($notification != null) {
            return (new NotificationResource($notification))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new NotificationResource($notification))->additional([
            'errors' => ['notification' => ['Notification not found!']],
        ])->response()->setStatusCode(404);
    }

}
