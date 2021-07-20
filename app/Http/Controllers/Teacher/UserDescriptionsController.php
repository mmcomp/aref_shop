<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserDescriptionCreateRequest;
use App\Http\Requests\UserDescriptionEditRequest;
use App\Http\Resources\UserDescriptionResource;
use App\Http\Resources\UserDescriptionCollection;
use App\Http\Requests\UserVideoSessionHomeworkRequest;
use App\Models\UserDescription;
use App\Models\UserVideoSessionHomework;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class UserDescriptionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        
        $teacher_id = Auth::user()->id;
        $user_descriptions = UserDescription::where('is_deleted', false)->whereHas('userVideoSessionHomework', function($query) use($teacher_id){
            $query->where('teachers_users_id', $teacher_id)->where('is_deleted', false); 
        })->OrderBy('id', 'desc')->get();
        return (new UserDescriptionCollection($user_descriptions))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\UserDescriptionCreateRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserDescriptionCreateRequest $request)
    {
        
        $user_video_session_homeworks_id = $request->input('user_video_session_homeworks_id');
        $description = $request->input('description');
        $users_id = Auth::user()->id;
        $userDescription = UserDescription::create([
           'user_video_session_homeworks_id' => $user_video_session_homeworks_id,
           'description' => $description,
           'users_id' => $users_id
        ]);
        $user_video_session_homework = UserVideoSessionHomework::find($user_video_session_homeworks_id);
        $user_video_session_homework->teachers_users_id = $users_id;
        $user_video_session_homework->save();
        return (new UserDescriptionResource($userDescription))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\UserVideoSessionHomeWorkRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id,  UserVideoSessionHomeworkRequest $request)
    {
        
        $teacher_id = Auth::user()->id;
        $user_video_session_homeworks_id = $request->input('user_video_session_homeworks_id');
        $userDescription = UserDescription::where('is_deleted', false)->where('user_video_session_homeworks_id', $user_video_session_homeworks_id)->whereHas('userVideoSessionHomework', function($query) use($teacher_id){
            $query->where('teachers_users_id', $teacher_id);  
        })->find($id);
        if($userDescription != null) {
            return (new UserDescriptionResource($userDescription))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200); 
        }
        return (new UserDescriptionResource(null))->additional([
            'errors' => ["not_found" => ["The user_description not found"]],
        ])->response()->setStatusCode(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\UserDescriptionEditRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserDescriptionEditRequest $request, $id)
    {
        
        $teacher_id = Auth::user()->id;
        $userDescription = UserDescription::where('is_deleted', false)->whereHas('userVideoSessionHomework', function($query) use($teacher_id){
            $query->where('teachers_users_id', $teacher_id)->where('is_deleted', false); 
        })->find($id);
        if($userDescription != null) {
            $userDescription->user_video_session_homeworks_id = $request->input("user_video_session_homeworks_id") ? $request->input('user_video_session_homeworks_id') : $userDescription->user_video_session_homeworks_id;
            $userDescription->description = $request->input("description") ? $request->input('description') : $userDescription->description;
            $userDescription->save();
            try {
                $userDescription->save();
                return (new UserDescriptionResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(200);
            } catch (Exception $e) {
                Log::info('fails in UserDescriptionsController/update ' . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new UserDescriptionResource(null))->additional([
                        'errors' => ['fail' => ['fails in UserDescriptionsController/update' . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new UserDescriptionResource(null))->additional([
                        'errors' => ['fail' => ['fails in UserDescriptionsController/update']],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new UserDescriptionResource(null))->additional([
            'errors' => ["not_found" => ["user description not found!"]],
        ])->response()->setStatusCode(200);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        
        $teacher_id = Auth::user()->id;
        $userDescription = UserDescription::where('is_deleted', false)->whereHas('userVideoSessionHomework', function($query) use($teacher_id){
            $query->where('teachers_users_id', $teacher_id)->where('is_deleted', false); 
        })->find($id);
        if($userDescription != null) {
            $userDescription->delete();
            return (new UserDescriptionResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(204);
        }
    }
}
