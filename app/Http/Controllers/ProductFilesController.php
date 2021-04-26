<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileCreateRequest;
use App\Http\Resources\ProductVideoSessionFileResource;
use App\Models\File;
use App\Models\ProductDetailVideo;
use App\Models\ProductFile;
use App\Models\VideoSessionFile;
use App\Utils\UploadImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Exception;
use Log;

class ProductFilesController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\FileCreateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FileCreateRequest $request)
    {

        $upload_image = new UploadImage;
        $file = File::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'file_path' => $upload_image->getImage($request->file('file'), 'public/uploads/files'),
            'users_id' => Auth::user()->id
        ]);
        $products_id = $request->input('products_id');
        $product_detail_videos_id = $request->input('product_detail_videos_id');
        if ($product_detail_videos_id || ($product_detail_videos_id && $products_id)) {
            $productDetailVideo = ProductDetailVideo::where('is_deleted', false)->find($request->input('product_detail_videos_id'));
            if ($productDetailVideo != null) {
                $product_file = ProductFile::create([
                    'products_id' => $productDetailVideo->products_id,
                    'users_id' => Auth::user()->id,
                    'files_id' => $file->id
                ]);
                VideoSessionFile::create([
                    'video_sessions_id' => $productDetailVideo->video_sessions_id,
                    'users_id' => Auth::user()->id,
                    'files_id' => $file->id
                ]);
                return (new ProductVideoSessionFileResource($product_file))->additional([
                    'error' => null,
                ])->response()->setStatusCode(201);
            }
        }
        if ($products_id) {
            $product_file = ProductFile::create([
                'products_id' => $request->input('products_id'),
                'users_id' => Auth::user()->id,
                'files_id' => $file->id,
            ]);
            return (new ProductVideoSessionFileResource($product_file))->additional([
                'error' => null,
            ])->response()->setStatusCode(201);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $file = FILE::where('is_deleted', false)->find($id);
        if ($file != null) {
            $product_file = ProductFile::where('files_id', $file->id)->first();
            $video_session_file = VideoSessionFile::where('files_id', $file->id)->first();
            $theFile = str_replace("storage", "public", $file->file_path);
            $file->file_path = null;
            if (Storage::exists($theFile)) {
                Storage::delete($theFile);
                $file->is_deleted = 1;
                if ($product_file != null) {
                    $product_file->delete();
                }
                if ($video_session_file != null) {
                    $video_session_file->delete();
                }
                try {
                    $file->save();
                    return (new ProductVideoSessionFileResource(null))->additional([
                        'error' => null,
                    ])->response()->setStatusCode(204);
                } catch (Exception $e) {
                    Log::info("fails in saving file delete file in ProductFileController " . json_encode($e));
                    if (env('APP_ENV') == "development") {
                        return (new ProductVideoSessionFileResource(null))->additional([
                            'error' => "fails in saving file delete file in ProductFileController " . json_encode($e),
                        ])->response()->setStatusCode(500);
                    } elseif (env('APP_ENV') == "production") {
                        return (new ProductVideoSessionFileResource(null))->additional([
                            'error' => "fails in saving file delete file in ProductFileController ",
                        ])->response()->setStatusCode(500);
                    }
                }
            }
        }
        return (new ProductVideoSessionFileResource(null))->additional([
            'error' => 'File not found!',
        ])->response()->setStatusCode(404);
    }
}
