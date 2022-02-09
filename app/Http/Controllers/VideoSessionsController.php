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
use App\Models\UserProduct;
use App\Utils\Buying;
use App\Models\ProductDetailVideo;
use App\Models\ProductDetailPackage;
use App\Models\Product;
use App\Models\UserVideoSession;
use Carbon\Carbon;
use App\Utils\RaiseError;
use App\Utils\UpdatePreviousByers;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Log;
use App\Http\Requests\DisableChatRequest;
use App\Http\Resources\ChatMessageResource;
use Illuminate\Support\Facades\Redis;

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
        $sort_dir = "desc";
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sort_dir = $request->get('sort_dir');
        }
        if ($request->get('per_page') == "all") {
            $videoSessions = VideoSession::where('is_deleted', false)->orderBy($sort, $sort_dir)->get();
        } else {
            $videoSessions = VideoSession::where('is_deleted', false)->orderBy($sort, $sort_dir)->paginate(env('PAGE_COUNT'));
        }
        return (new VideoSessionsCollection($videoSessions))->additional([
            'errors' => null,
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
            'errors' => null,
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
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new VideoSessionsResource($video_session))->additional([
            'errors' => ['video_session' => ['VideoSession not found!']],
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
                'errors' => null,
            ])->response()->setStatusCode(200);
        }
        return (new VideoSessionsResource(null))->additional([
            'errors' => ['video_session' => ['VideoSession not found!']],
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
                    'errors' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('failed in VideoSessionsController/destory', json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new VideoSessionsResource(null))->additional([
                        'errors' => ["fail" => ['VideoSession deleting failed! ' . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new VideoSessionsResource(null))->additional([
                        'errors' => ['fail' => ['VideoSession deleting failed!']],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new VideoSessionsResource(null))->additional([
            'errors' => ['video_session' => ['VideoSession not found!']],
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
        //$completed_orders = Order::where('status', 'ok')->get();
        $data = [];
        $data1 = [];
        $userIds = UserProduct::where('products_id', $request->input('products_id'))->whereHas('product', function ($query) {
            $query->where('type', 'video')->where('is_deleted', false);
        })->where('partial', 0)->pluck('users_id');
        foreach ($userIds as $id) {
            foreach ($video_session_ids as $video_session_id) {
                $found_user_video_session = UserVideoSession::where('users_id', $id)->where('video_sessions_id', $video_session_id)->first();
                if (!$found_user_video_session) {
                    $data[] = [
                        'users_id' => $id,
                        'video_sessions_id' => $video_session_id,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ];
                }
            }
        }
        UserVideoSession::insert($data);
        $user_product_packages = UserProduct::where('products_id', $request->input('products_id'))->whereHas('product', function ($query) {
            $query->where('type', 'package')->where('is_deleted', false);
        })->where('partial', 0)->pluck('products_id');
        $child_products = ProductDetailPackage::where('is_deleted', false)->whereIn('products_id', $user_product_packages)->pluck('child_products_id');
        $userIdsForPackages = UserProduct::where('products_id', $request->input('products_id'))->whereHas('product', function ($query) {
            $query->where('type', 'package')->where('is_deleted', false);
        })->where('partial', 0)->pluck('users_id');
        $childData = [];
        foreach ($userIdsForPackages as $userId) {
            foreach ($child_products as $child_product) {
                $found_product = UserProduct::where('users_id', $userId)->where('products_id', $child_product)->first();
                if (!$found_product) {
                    $childData[] = [
                        'users_id' => $userId,
                        'products_id' => $child_product,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s")
                    ];
                }
                $p = Product::where('is_deleted', false)->where('id', $child_product)->first();
                if ($p->type == 'video') {
                    $videoSessionIds = ProductDetailVideo::where('is_deleted', false)->where('products_id', $p->id)->pluck('video_sessions_id')->toArray();
                    foreach ($videoSessionIds as $video_session_id) {
                        $found_user_video_session = UserVideoSession::where('users_id', $userId)->where('video_sessions_id', $video_session_id)->first();
                        if (!$found_user_video_session) {
                            $data1[] = [
                                'users_id' => $userId,
                                'video_sessions_id' => $video_session_id,
                                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                            ];
                        }
                    }
                }
            }
        }

        UserProduct::insert($childData);
        UserVideoSession::insert($data1);
        return (new VideoSessionsResource(null))->additional([
            'errors' => null,
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
            'is_aparat' => (($request->input('is_aparat') !== null) ? $request->input('is_aparat') : 0),
        ]);
        ProductDetailVideo::create([
            "single_purchase" => $request->input('single_purchase') ? $request->input('single_purchase') : 0,
            "price" => $request->input("price"),
            "products_id" => $request->input("products_id"),
            "video_sessions_id" => $video_session->id,
        ]);
        $found_product_detail_video = ProductDetailVideo::where('is_deleted', false)->where('products_id', $request->input('video_sessions_id'))->where('video_sessions_id', $video_session->id)->first();
        $raiseError->validationError($found_product_detail_video, ['product_detail_video' => ['The product_detail_video is already recorded!']]);
        //$updatePreviousBuyers = new UpdatePreviousByers;
        //$updatePreviousBuyers->create(false, $request, $video_session->id);
        $data = [];
        $userIds = UserProduct::where('products_id', $request->input('products_id'))->where('partial', 0)->pluck('users_id');
        foreach ($userIds as $id) {
            $found_user_video_session = UserVideoSession::where('users_id', $id)->where('video_sessions_id', $video_session->id)->first();
            if (!$found_user_video_session) {
                $data[] = [
                    'users_id' => $id,
                    'video_sessions_id' => $video_session->id,
                    'created_at' =>  Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' =>  Carbon::now()->format('Y-m-d H:i:s')
                ];
            }
        }
        UserVideoSession::insert($data);

        $child_products = ProductDetailPackage::where('is_deleted', false)->where('products_id', $request->input('products_id'))->pluck('child_products_id');
        $userIdsForPackages = UserProduct::where('products_id', $request->input('products_id'))->whereHas('product', function ($query) {
            $query->where('type', 'package')->where('is_deleted', false);
        })->where('partial', 0)->pluck('users_id');
        $childData = [];
        $data1 = [];
        foreach ($userIdsForPackages as $userId) {
            foreach ($child_products as $child_product) {
                $found_product = UserProduct::where('users_id', $userId)->where('products_id', $child_product)->first();
                if (!$found_product) {
                    $childData[] = [
                        'users_id' => $userId,
                        'products_id' => $child_product,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s")
                    ];
                }
                $p = Product::where('is_deleted', false)->where('id', $child_product)->first();
                if ($p->type == 'video') {
                    $videoSessionIds = ProductDetailVideo::where('is_deleted', false)->where('products_id', $p->id)->pluck('video_sessions_id')->toArray();
                    foreach ($videoSessionIds as $video_session_id) {
                        $found_user_video_session = UserVideoSession::where('users_id', $userId)->where('video_sessions_id', $video_session_id)->first();
                        if (!$found_user_video_session) {
                            $data1[] = [
                                'users_id' => $userId,
                                'video_sessions_id' => $video_session_id,
                                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                            ];
                        }
                    }
                }
            }
        }
        UserProduct::insert($childData);
        UserVideoSession::insert($data1);
        return (new VideoSessionsResource(null))->additional([
            'errors' => null,
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
            if (!in_array($date, $fiveDaysAfterTheDate) && !in_array($date, $fiveDaysBeforeTheDate) && $request->input('date') != $video_sesssion->start_date) {
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
                "free_conference_start_mode" => $request->input('free_conference_start_mode'),
                "free_conference_description" => $request->input('free_conference_description'),
                "free_conference_before_start_text" => $request->input('free_conference_before_start_text'),
            ]);
            $video_sesssion->update([
                'start_date' => $request->input('date'),
                'start_time' => $request->input('from_time'),
                'end_time' => $request->input('to_time'),
                'price' => $request->input('price'),
                'video_session_type' => $request->input('video_session_type') ? $request->input('video_session_type') : 'offline',
                'video_link' => $request->input('video_link'),
                'is_aparat' => $request->input('is_aparat'),
            ]);
        }
        $raiseError->ValidationError(!$product_detail_video->videoSession, ['extraordinary' => ['No video Session is saved for the product']]);
        return (new VideoSessionsResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }
    public function disabledVideoSessions()
    {

        $video_sessions = Redis::hGetAll('disable_video_session');
        $disabled_video_sessions = [];
        if ($video_sessions) {
            foreach ($video_sessions as $index => $item) {
                $disabled_video_sessions[] = VideoSession::where('is_deleted', false)->find($index);
            }
        }
        return (new VideoSessionsCollection($disabled_video_sessions))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
