<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\ProductDetailVideosForFreeSessionsCollection;
use App\Http\Resources\User\ProductDetailVideosForFreeSessionsResource;
use App\Http\Resources\User\ProductDetailVideosForTodaySessionsCollection;
use App\Models\ProductDetailVideo;
use App\Utils\GetNameOfSessions;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\UserVideoSession;
use App\Models\UserProduct;
use Log;

class VideoSessionsController extends Controller
{
    function allFreeSessions()
    {
        return $this->freeSessions(true);    
    }

    /**
     * All free sessions
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function freeSessions($showAll = false)
    {

        $user_phone = Auth::user()->email;
        $whiteListed = in_array($user_phone, explode(",", env('EXECPTIONAL_USER')));

        $final_arr = [];
        $getNameOfSessions = new GetNameOfSessions;
        $free_sessions = ProductDetailVideo::where('is_deleted', false)
            ->where(function ($query) use ($whiteListed) {
                $query->orWhere(function ($q1) use ($whiteListed) {
                    $q1->where('price', 0)->whereHas('videoSession', function ($q2) use ($whiteListed) {
                        $q2->where('is_deleted', false);
                        if (!$whiteListed)
                            $q2 = $q2->where('start_date', '>=', env('USER_PRODUCT_DATE'));
                    });
                })->orWhere(function ($q) use ($whiteListed) {
                    $q->where('price', null)->whereHas('videoSession', function ($q2)  use ($whiteListed) {
                        $q2->where('price', 0)->where('is_deleted', false);
                        if (!$whiteListed)
                            $q2 = $q2->where('start_date', '>=', env('USER_PRODUCT_DATE'));
                    });
                });
            })->whereHas('product', function ($q) {
                $q->where('is_deleted', false);
            });
        if (!$showAll)
            $free_sessions->where('is_hidden', 0);
        $free_sessions = $free_sessions->get();
        for ($i = 0; $i < count($free_sessions); $i++) {
            $output = $getNameOfSessions->getProductDetailVideos($free_sessions[$i]->product, Auth::user()->id);
            for ($j = 0; $j < count($output); $j++) {
                if ($output[$j]->id == $free_sessions[$i]->id) {
                    $free_sessions[$i] = $output[$j];
                }
            }
        }
        foreach ($free_sessions as $item) {
            $final_arr[] = (new ProductDetailVideosForFreeSessionsResource($item))->check(true);
        }

        return (new ProductDetailVideosForFreeSessionsCollection($free_sessions))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
    /**
     * today video sessions
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function todaySessions()
    {

        $today_date = Carbon::now()->format('Y-m-d');
        // $user_products = UserProduct::where('users_id', Auth::user()->id)->pluck('products_id')->toArray();
        $today_sessions = ProductDetailVideo::where('is_deleted', false)/*->whereIn('products_id', $user_products)*/->whereHas('videoSession', function ($query) use ($today_date) {
            $query->where('start_date', $today_date);
        })->whereHas('product', function ($q) {
            $q->where('is_deleted', false);
        })->get();
        $bouth_video_sessions = UserVideoSession::where('users_id', Auth::user()->id)
            ->whereIn('video_sessions_id', $today_sessions->pluck('video_sessions_id'))
            ->pluck('video_sessions_id')->toArray();
        for ($j = 0; $j < count($today_sessions); $j++) {

            $today_sessions[$j]->buyed_before = in_array($today_sessions[$j]->video_sessions_id, $bouth_video_sessions);
        }

        return (new ProductDetailVideosForTodaySessionsCollection($today_sessions))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
