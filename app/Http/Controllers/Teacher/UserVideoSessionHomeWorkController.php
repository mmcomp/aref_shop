<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserVideoSessionHomeWorkResource;
use App\Http\Requests\Teacher\AddDescriptionRequest;
use App\Models\UserVideoSessionHomework;

class UserVideoSessionHomeWorkController extends Controller
{
    
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
        $user_video_session_homework->teacher_description = $description;
        $user_video_session_homework->save();
        return (new UserVideoSessionHomeWorkResource($user_video_session_homework))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
