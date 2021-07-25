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

class VideoSessionsController extends Controller
{

    /**
     * All free sessions
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function freeSessions()
    {

        $final_arr = [];
        $getNameOfSessions = new GetNameOfSessions;
        $free_sessions = ProductDetailVideo::where('is_deleted', false)->where(function ($query) {
            $query->orWhere(function ($q1) {
                $q1->where('price', 0)->whereHas('videoSession', function ($q2) {
                    $q2->where('is_deleted', false);
                });
            })->orWhere(function ($q) {
                $q->where('price', null)->whereHas('videoSession', function ($q2) {
                    $q2->where('price', 0)->where('is_deleted', false);
                });
            });
        })->whereHas('product', function ($q) {
            $q->where('is_deleted', false);
        })->get();
        for($i = 0; $i < count($free_sessions); $i++) {
            $output = $getNameOfSessions->getProductDetailVideos($free_sessions[$i]->product, Auth::user()->id);
            for($j = 0; $j < count($output); $j++) {
                if($output[$j]->id == $free_sessions[$i]->id) {
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
        $today_sessions = ProductDetailVideo::where('is_deleted', false)->whereHas('videoSession', function ($query) use ($today_date) {
            $query->where('start_date', $today_date);
        })->whereHas('product', function ($q) {
            $q->where('is_deleted', false);
        })->get();
        return (new ProductDetailVideosForTodaySessionsCollection($today_sessions))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
