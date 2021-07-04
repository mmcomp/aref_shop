<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserDescriptionCreateRequest;
use App\Http\Requests\UserDescriptionEditRequest;
use App\Http\Resources\UserDescriptionResource;
use App\Http\Resources\UserDescriptionCollection;
use App\Models\UserDescription;
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
        
        $user_id = Auth::user()->id;
        $user_descriptions = UserDescription::where('is_deleted', false)->OrderBy('id', 'desc')->where('users_id', $user_id)->get();
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
        return (new UserDescriptionResource($userDescription))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        
        $user_id = Auth::user()->id;
        $userDescription = UserDescription::where('is_deleted', false)->where('users_id', $user_id)->find($id)/*orWhereHas('userVideoSessionHomework.userVideoSession', function($query) use($user_id){
            $query->where('users_id', $user_id);
        })*/;
        if($userDescription != null) {
            return (new UserDescriptionResource($userDescription))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200); 
        }
        return (new UserDescriptionResource(null))->additional([
            'errors' => ["not_found" => ["The user_description id not found"]],
        ])->response()->setStatusCode(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        
        $userDescription = UserDescription::where('is_deleted', false)->find($id);
        if($userDescription != null) {
            $userDescription->is_deleted = 1;
            $userDescription->save();
            return (new UserDescriptionResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(204);
        }
    }
}
