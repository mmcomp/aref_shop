<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ConcatHomeworkRequest;
use App\Http\Requests\User\DeleteHomeworkRequest;
use App\Http\Requests\User\AddDescriptionRequest;
use App\Http\Resources\UserVideoSessionHomeWorkResource;
use App\Models\UserVideoSessionHomework;
use App\Models\UserDescription;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Utils\UploadImage;

class UserVideoSessionHomeWorkController extends Controller
{

    /**
     * concat homework to video_session
     *
     * @param int $id
     * @param  ConcatHomeworkRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ConcatHomeWorkToSession(int $id, ConcatHomeworkRequest $request)
    {

        $user_id = Auth::user()->id;
        $upload_file = new UploadImage;
        if ($request->file('file')) {
            $user_video_session_homework = UserVideoSessionHomework::create([
                'user_video_sessions_id' => $id,
                'file' => $upload_file->getImage($request->file('file'), "public/homeworks/" . $user_id, "homework"),
            ]);
            return (new UserVideoSessionHomeWorkResource($user_video_session_homework))->additional([
                'errors' => null,
            ])->response()->setStatusCode(201);
        }
    }
    /**
     * Delete homework file 
     *
     * @param  int  $id
     * @param  DeleteHomeworkRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function DeleteHomework(int $id, DeleteHomeworkRequest $request)
    {

        $user_video_session_homework = UserVideoSessionHomework::where('is_deleted', false)->find($id);
        $file = str_replace("storage", "public", $user_video_session_homework->file);
        if (Storage::exists($file)) {
            Storage::delete($file);
        }
        $user_description = UserDescription::where('user_video_session_homeworks_id', $user_video_session_homework->id)->first();
        if($user_description == null) {
            $user_video_session_homework->is_deleted = 1;
            $user_video_session_homework->save();
            return (new UserVideoSessionHomeWorkResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(204);
        }
        return (new UserVideoSessionHomeWorkResource(null))->additional([
            'errors' => ["can_not_be_deleted" => ["You can not delete homework because there is comment on this!"]],
        ])->response()->setStatusCode(406);  
    }
    /**
     * add description 
     *
     * @param  int  $id
     * @param  AddDescriptionRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addDescription(int $id, AddDescriptionRequest $request)
    {

        $user_video_session_homework = UserVideoSessionHomework::where('is_deleted', false)->find($id);
        $description = $request->input("description");        
        $user_video_session_homework->description = $description;
        $user_video_session_homework->save();
        return (new UserVideoSessionHomeWorkResource($user_video_session_homework))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
