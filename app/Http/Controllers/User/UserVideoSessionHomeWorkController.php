<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\ConcatHomeworkRequest;
use App\Utils\UploadImage;
use App\Models\UserVideoSession;
use App\Models\UserVideoSessionHomework;

class UserVideoSessionHomeWorkController extends Controller
{
    
    /**
     * concat homework to video_session
     *
     * @param  ConcatHomeworkRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function ConcatHomeWorkToSession(ConcatHomeworkRequest $request, $id)
    {

        $user_video_session = UserVideoSession::find($id);
        if($user_video_session != null) {
            $upload_file = new UploadImage;
            if ($request->file('file')) {
                //$upload_image->imageNullablility($product->second_image_path);
               // $->second_image_path = $upload_image->getImage($request->file('second_image_path'), "public/uploads", "second");
                UserVideoSessionHomework::create([
                   'user_video_sessions_id' => $user_video_session->id,

                ]);
            }
        }

        
    }
    /**
     * Set second image for product
     *
     * @param  App\Http\Requests\ProductImageRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function setSecondImage(ProductImageRequest $request, $id)
    {

        $product = Product::where('is_deleted', false)->find($id);
        if ($product != null) {
            $upload_image = new UploadImage;
            if ($request->file('second_image_path')) {
                $upload_image->imageNullablility($product->second_image_path);
                $product->second_image_path = $upload_image->getImage($request->file('second_image_path'), "public/uploads", "second");
            }
            try {
                $product->save();
                return (new ProductResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(201);
            } catch (Exception $e) {
                Log::info("fails in saving image " . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new ProductResource(null))->additional([
                        'errors' => ["fail" => ["fails in saving main image" . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new ProductResource(null))->additional([
                        'errors' => ["fail" => ["fails in saving main image"]],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new ProductResource(null))->additional([
            'errors' => ['product' => ['Product not found!']],
        ])->response()->setStatusCode(404);
    }
}
