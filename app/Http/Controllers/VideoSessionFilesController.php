<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoSessionFileCreateRequest;
use App\Http\Requests\CreateNewVideoSessionFileByGettingFileInfo;
use App\Http\Resources\VideoSessionFileResource;
use App\Models\File;
use App\Models\ProductDetailVideo;
use App\Models\ProductFile;
use App\Models\VideoSessionFile;
use App\Utils\RaiseError;
use App\Utils\UploadImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VideoSessionFilesController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\VideoSessionFileCreateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(VideoSessionFileCreateRequest $request)
    {

        $upload_image = new UploadImage;
        $raiseError = new RaiseError;
        $file = File::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'file_path' => $upload_image->getImage($request->file('file'), 'public/uploads/files'),
            'users_id' => Auth::user()->id,
        ]);
        $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($request->input('product_detail_videos_id'));
        ProductFile::updateOrCreate([
            'products_id' => $product_detail_video->product ? $product_detail_video->products_id : $raiseError->ValidationError(!$product_detail_video->product, ['products_id' => ['The product of this product_detail_videos_id is not valid!']]),
            'users_id' => Auth::user()->id,
            'files_id' => $file->id,
        ]);
        $video_session_file = VideoSessionFile::updateOrCreate([
            'video_sessions_id' => $product_detail_video->videoSession ? $product_detail_video->video_sessions_id : $raiseError->ValidationError(!$product_detail_video->videoSession, ['video_sessions_id' =>['The videoSession of this product_detail_videos_id is not valid!']]),
            'users_id' => Auth::user()->id,
            'files_id' => $file->id,
        ]);
        return (new VideoSessionFileResource($video_session_file))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $video_session_file = VideoSessionFile::find($id);
        if ($video_session_file != null) {
            $video_session_file->delete();
            return (new VideoSessionFileResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(204);
        }
    }
     /**
     * add a new record to VideoSessionFiles table by getting files_id and product_detail_videos_id 
     *
     * @param  int  $id
     * @param  \App\Http\Requests\CreateNewVideoSessionFileByGettingFileInfo $request
     * @return \Illuminate\Http\Response
     */
    public function createNewVideoSessionFile(CreateNewVideoSessionFileByGettingFileInfo $request)
    {
 
        $raiseError = new RaiseError;
        $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($request->input('product_detail_videos_id'));
        if($product_detail_video->videoSession){
            $videoSessionId = $product_detail_video->video_sessions_id;
            $found = VideoSessionFile::where('video_sessions_id', $videoSessionId)->where('files_id', $request->input('files_id'))->first();
            if(!$found){
                $video_session_file = VideoSessionFile::create([
                    'video_sessions_id' => $videoSessionId,
                    'files_id' => $request->input('files_id'),
                    'users_id' => Auth::user()->id
                 ]);
                 return (new VideoSessionFileResource($video_session_file))->additional([
                     'errors' => null,
                 ])->response()->setStatusCode(201);
            } 
            $raiseError->ValidationError($found,['exists' => ['This record is already saved!']]);
           
        } 
        $raiseError->ValidationError(!$product_detail_video->videoSession, ['video_sessions_id' => ['There is no video session for the product_detail_video']]);
    }
}
