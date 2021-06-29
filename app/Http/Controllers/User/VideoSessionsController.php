<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\ProductDetailVideosForFreeSessionsCollection;
use App\Models\ProductDetailVideo;

class VideoSessionsController extends Controller
{
    
     /**
     * All free sessions
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function freeSessions()
    {

        $free_sessions = ProductDetailVideo::where('is_deleted', false)->where(function($query) {
           $query->orWhere('price', 0)->orWhere(function($q){
                $q->where('price', null)->whereHas('videoSession', function($q2){
                    $q2->where('price', 0); 
                 });
           });
        })->get();

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
        $today_sessions = ProductDetailVideo::where('is_deleted', false)->where(function($query) {
            $query->orWhere('price', 0)->orWhere(function($q){
                 $q->where('price', null)->whereHas('videoSession', function($q2){
                     $q2->where('price', 0); 
                  });
            });
         })->get();
    }
}
