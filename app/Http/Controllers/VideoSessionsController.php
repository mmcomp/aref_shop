<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddVideosAccordingToUserInputsRequest;
use App\Http\Requests\VideoSessionCreateRequest;
use App\Http\Requests\VideoSessionEditRequest;
use App\Http\Requests\VideoSessionIndexRequest;
use App\Http\Resources\VideoSessionsCollection;
use App\Http\Resources\VideoSessionsResource;
use App\Models\VideoSession;
use App\Models\ProductDetailVideo;
use Exception;
use Log;

class VideoSessionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Requests\VideoSessionIndexRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(VideoSessionIndexRequest $request)
    {

        $sort = "id";
        $type = "desc";
        if ($request->get('type') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $type = $request->get('type');
        }
        if ($request->get('per_page') == "all") {
            $videoSessions = VideoSession::where('is_deleted', false)->orderBy($sort, $type)->get();

        } else {
            $videoSessions = VideoSession::where('is_deleted', false)->orderBy($sort, $type)->paginate(env('PAGE_COUNT'));
        }
        return (new VideoSessionsCollection($videoSessions))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\VideoSessionCreateRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(VideoSessionCreateRequest $request)
    {

        $video_session = VideoSession::create($request->all());
        return (new VideoSessionsResource($video_session))->additional([
            'error' => null,
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

        $video_session = VideoSession::where('is_deleted', false)->find($id);
        if ($video_session != null) {
            return (new VideoSessionsResource($video_session))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new VideoSessionsResource($video_session))->additional([
            'error' => 'VideoSession not found!',
        ])->response()->setStatusCode(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\VideoSessionEditRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(VideoSessionEditRequest $request, $id)
    {

        $video_session = VideoSession::where('is_deleted', false)->find($id);
        if ($video_session != null) {
            $video_session->update($request->all());
            return (new VideoSessionsResource(null))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        }
        return (new VideoSessionsResource(null))->additional([
            'error' => 'VideoSession not found!',
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

        $video_session = VideoSession::where('is_deleted', false)->find($id);
        if ($video_session != null) {
            $video_session->is_deleted = 1;
            try {
                $video_session->save();
                return (new VideoSessionsResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in VideoSessionsController/destory', json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new VideoSessionsResource(null))->additional([
                        'error' => 'VideoSession deleting failed! ' . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new VideoSessionsResource(null))->additional([
                        'error' => 'VideoSession deleting failed!',
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new VideoSessionsResource(null))->additional([
            'error' => 'VideoSession not found!',
        ])->response()->setStatusCode(404);
    }

    public function getNameOfTheDate($date)
    {

        $timestamp = strtotime($date);
        $day = date('l', $timestamp);
        return $day;
    }
    /**
     * Insert into video_sessions_table & product_detail_videos_table according to description
     *
     * @param  \App\Http\Requests\AddVideosAccordingToUserInputsRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function AddVideosAccordingToUserInputs(AddVideosAccordingToUserInputsRequest $request)
    {

        $date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $days = $request->input('days');
        while (strtotime($date) <= strtotime($to_date)) {
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
            if(in_array($this->getNameOfTheDate($date), $days)) {
                $v = VideoSession::create([
                    "start_date" => $date,
                    "start_time" => $request->input("from_time"),
                    "end_time" => $request->input("to_time"),
                    "price" => $request->input("per_price"),
                    "video_session_type" => "offline",
                    "is_hidden" => 0
                ]);
                ProductDetailVideo::create([
                    "price" => $request->input("per_price"),
                    "products_id" => $request->input("products_id"),
                    "video_sessions_id" => $v->id
                ]);
            }
        }
        return (new VideoSessionsResource(null))->additional([
            'error' => null,
        ])->response()->setStatusCode(201);
    }
}
