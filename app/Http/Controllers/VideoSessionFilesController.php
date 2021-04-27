<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoSessionFileCreateRequest;
use App\Http\Resources\VideoSessionFileResource;
use App\Utils\UploadImage;
use App\Models\File;
use App\Models\VideoSessionFile;
use App\Models\ProductDetailVideo;
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
        $file = File::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'file_path' => $upload_image->getImage($request->file('file'), 'public/uploads/files'),
            'users_id' => Auth::user()->id,
        ]);
        $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($request->input('product_detail_videos_id'));
        $video_session_file = VideoSessionFile::updateOrCreate([
            'video_sessions_id' => $product_detail_video->videoSession ? $product_detail_video->video_sessions_id : 0,
            'users_id' => Auth::user()->id,
            'files_id' => $file->id,
        ]);
        return (new VideoSessionFileResource($video_session_file))->additional([
            'error' => null,
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
        
        $file = FILE::find($id);
        if ($file != null) {
            $video_session_file = VideoSessionFile::where('files_id', $file->id)->first();
            $theFile = str_replace("storage", "public", $file->file_path);
            if (Storage::exists($theFile)) {
                Storage::delete($theFile);
                $file->delete();
                $video_session_file->delete();
                return (new VideoSessionFileResource(null))->additional([
                    'error' => 'File successfully deleted!',
                ])->response()->setStatusCode(204);  
            }
        }
        return (new VideoSessionFileResource(null))->additional([
            'error' => 'File not found!',
        ])->response()->setStatusCode(404);
    }
}
