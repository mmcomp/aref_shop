<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationCreateRequest;
use App\Http\Requests\NotificationUpdateRequest;
use App\Http\Resources\NotificationCollection;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Exception;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $notifications = Notification::where('is_deleted', false)->orderBy('date', 'desc')->get();
        return (new NotificationCollection($notifications))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\NotificationCreateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(NotificationCreateRequest $request)
    {

        $notification = Notification::create([
            'title' => $request->title,
            'content' => $request->content,
            'published' => $request->published ? $request->published : 0,
            'date' => $request->date
        ]);
        return (new NotificationResource($notification))->additional([
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

        $notification = Notification::where('is_deleted', false)->find($id);
        if ($notification != null) {
            return (new NotificationResource($notification))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new NotificationResource($notification))->additional([
            'errors' => ['notification' => ['Notification not found!']],
        ])->response()->setStatusCode(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\NotificationUpdateRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(NotificationUpdateRequest $request, $id)
    {

        $notification = Notification::where('is_deleted', false)->find($id);
        if ($notification != null) {
            $notification->update($request->all());
            return (new NotificationResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new NotificationResource(null))->additional([
            'errors' => ['notification' => ['Notification not found!']],
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

        $notification = Notification::where('is_deleted', false)->find($id);
        if ($notification != null) {
            $notification->is_deleted = 1;
            try {
                $notification->save();
                return (new NotificationResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in NotificationController/destory', json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new NotificationResource(null))->additional([
                        'errors' => ['fail' => ['Notification deleting failed! ' . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new NotificationResource(null))->additional([
                        'errors' => ['fail' => ['Notification deleting failed!']],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new NotificationResource(null))->additional([
            'errors' => ['notification' => ['Notification not found!']],
        ])->response()->setStatusCode(404);
    }
}
