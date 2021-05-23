<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddVideosAccordingToUserInputsRequest;
use App\Http\Requests\EditSingleSessionRequest;
use App\Http\Requests\InsertSingleSessionRequest;
use App\Http\Requests\VideoSessionCreateRequest;
use App\Http\Requests\VideoSessionEditRequest;
use App\Http\Requests\VideoSessionIndexRequest;
use App\Http\Resources\VideoSessionsCollection;
use App\Http\Resources\VideoSessionsResource;
use App\Models\Order;
use App\Models\VideoSession;
use App\Models\ProductDetailVideo;
use App\Models\UserVideoSession;
use Carbon\Carbon;
use App\Utils\RaiseError;
use App\Utils\UpdatePreviousByers;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
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
                UserVideoSession::where('video_sessions_id', $id)->delete();
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
        $video_session_ids = [];
        while (strtotime($date) <= strtotime($to_date)) {
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
            if (in_array($this->getNameOfTheDate($date), $days)) {
                $v = VideoSession::create([
                    "start_date" => $date,
                    "start_time" => $request->input("from_time"),
                    "end_time" => $request->input("to_time"),
                    "price" => $request->input("per_price"),
                    "video_session_type" => "offline",
                    "is_hidden" => 0,
                ]);
                ProductDetailVideo::create([
                    "single_purchase" => $request->input('single_purchase') ? $request->input('single_purchase') : 0,
                    "price" => $request->input("per_price"),
                    "products_id" => $request->input("products_id"),
                    "video_sessions_id" => $v->id,
                ]);
                $video_session_ids[] = $v->id;
            }
        }
        $completed_orders = Order::where('status', 'ok')->get();
        $data = [];
        foreach ($completed_orders as $order) {
            foreach ($order->orderDetail as $orderDetail) {
                if ($orderDetail->product->id == $request->input('products_id') && $orderDetail->all_videos_buy && $orderDetail->product->type == 'video') {
                    foreach ($video_session_ids as $vs_id) {
                        $found_user_video_session = UserVideoSession::where('users_id', $order->users_id)->where('video_sessions_id', $vs_id)->first();
                        if (!$found_user_video_session) {
                            $data[] = [
                                'video_sessions_id' => $vs_id,
                                'users_id' => $order->users_id,
                                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                            ];
                        }
                    }
                }
            }
        }
        UserVideoSession::insert($data);
        return (new VideoSessionsResource(null))->additional([
            'error' => null,
        ])->response()->setStatusCode(201);
    }
    /**
     * Insert single session into video_sessions_table & product_detail_videos_table
     *
     * @param  \App\Http\Requests\InsertSingleSessionRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function InsertSingleVideoSession(InsertSingleSessionRequest $request)
    {

        $raiseError = new RaiseError;
        $lastVideoSessionOfThatProduct = VideoSession::join('product_detail_videos', 'video_sessions.id', '=', 'product_detail_videos.video_sessions_id')
            ->where('product_detail_videos.is_deleted', false)
            ->where('video_sessions.is_deleted', false)
            ->where('products_id', $request->input('products_id'))
            ->where('video_sessions.start_date', '>', $request->input('date'))
            ->orderBy('video_sessions.start_date', 'desc')->first();
        $raiseError->validationError($lastVideoSessionOfThatProduct && !$request->input('extraordinary'), ['extraordinary' => ['The extraordinary field should be 1']]);

        $video_session = VideoSession::create([
            'start_date' => $request->input('date'),
            'start_time' => $request->input('from_time'),
            'end_time' => $request->input('to_time'),
            'price' => $request->input('price'),
            'video_session_type' => $request->input('video_session_type') ? $request->input('video_session_type') : 'offline',
            'video_link' => $request->input('video_link'),
        ]);
        $found_product_detail_video = ProductDetailVideo::where('is_deleted', false)->where('products_id', $request->input('video_sessions_id'))->where('video_sessions_id', $video_session->id)->first();
        $raiseError->validationError($found_product_detail_video, ['product_detail_video' => ['The product_detail_video is already recorded!']]);
        $updatePreviousBuyers = new UpdatePreviousByers;
        $updatePreviousBuyers->create(false, $request, $video_session->id);
        return (new VideoSessionsResource(null))->additional([
            'error' => null,
        ])->response()->setStatusCode(201);
    }
    /**
     * Edit single session into video_sessions_table & product_detail_videos_table
     *
     * @param  \App\Http\Requests\EditSingleSessionRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function EditSingleVideoSession(int $id, EditSingleSessionRequest $request)
    {

        $raiseError = new RaiseError;
        $fiveDaysAfterTheDate = [];
        $fiveDaysBeforeTheDate = [];
        $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($id);
        if ($product_detail_video->videoSession) {
            $date = strtotime($request->input("date"));
            $video_sesssion = VideoSession::where('is_deleted', false)->find($product_detail_video->video_sessions_id);
            for ($i = 1; $i <= 5; $i++) {
                $fiveDaysAfterTheDate[$i] = strtotime(date("Y-m-d", strtotime("+" . $i . "day", strtotime($video_sesssion->start_date))));
            }
            for ($i = 1; $i <= 5; $i++) {
                $fiveDaysBeforeTheDate[$i] = strtotime(date("Y-m-d", strtotime("-" . $i . "day", strtotime($video_sesssion->start_date))));
            }
            if (!in_array($date, $fiveDaysAfterTheDate) && !in_array($date, $fiveDaysBeforeTheDate)) {
                throw new HttpResponseException(
                    response()->json(['errors' => ['start_date' => 'You can change start_date just 5 days after or 5 days before!']], 422)
                );
            }
            $product_detail_video->update([
                "price" => $request->input("price"),
                "products_id" => $request->input("products_id"),
                "name" => $request->input('name'),
                "single_purchase" => $request->input('single_purchase'),
                "extraordinary" => $request->input('extraordinary'),
                "is_hidden" => $request->input("is_hidden") ? $request->input("is_hidden") : 0,
            ]);
            $video_sesssion->update([
                'start_date' => $request->input('date'),
                'start_time' => $request->input('from_time'),
                'end_time' => $request->input('to_time'),
                'price' => $request->input('price'),
                'video_session_type' => $request->input('video_session_type') ? $request->input('video_session_type') : 'offline',
                'video_link' => $request->input('video_link')
            ]);
        }
        $raiseError->ValidationError(!$product_detail_video->videoSession, ['extraordinary' => ['No video Session is saved for the product']]);
        return (new VideoSessionsResource(null))->additional([
            'error' => null,
        ])->response()->setStatusCode(201);
    }
}
