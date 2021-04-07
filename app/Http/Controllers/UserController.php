<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserBulkDeleteRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserEditRequest;
use App\Http\Requests\UserIndexRequest;
use App\Http\Requests\UserSetAvatarRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Utils\UploadImage;
use Illuminate\Support\Facades\DB;
use Exception;
use Log;

class UserController extends Controller
{
     /**
     * Create a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     *
     * @param   App\Http\Requests\UserIndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(UserIndexRequest $request)
    {

        $paginated_users = User::where('is_deleted', false)->paginate($request->page_count);
        return (new UserCollection($paginated_users))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);
    }
    /**
     * get User Id and display all his/her properties
     *
     * @param  id $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser($id)
    {

        $user = User::where('is_deleted', false)->find($id);
        if ($user != null) {
            return (new UserResource($user))->additional([
                'error' => null,
            ])->response()->setStatusCode(200);
        } else {
            return (new UserResource($user))->additional([
                'error' => 'User not found!',
            ])->response()->setStatusCode(404);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param App\Http\Requests\UserCreateRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(UserCreateRequest $request)
    {

        $userData = array_merge($request->validated(), ['pass_txt' => $request->password,'password' => bcrypt($request->password), 'groups_id' => 2, 'avatar_path' => ""]);
        $user = User::create($userData);
        return (new UserResource($user))->additional([
            'error' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\UserEditRequest  $request
     * @param id $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id, UserEditRequest $request)
    {

        $user = User::where('id', $id)->first();
        if ($user != null) {
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            if($request->password){
                $user->password = bcrypt($request->password);
                $user->pass_txt = $request->password;
            }
            if((int)$id != $request->referrer_users_id){
                $user->referrer_users_id = $request->referrer_users_id;
            } else {
                return (new UserResource(null))->additional([
                    'error' => 'referrers_users_id and user id should be different!',
                ])->response()->setStatusCode(406);
            }
            $user->address = $request->address;
            $user->postall = $request->postall;
            $user->cities_id = $request->cities_id;
            try {
                $user->save();
                return (new UserResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(200);
            } catch (Exception $e) {
                Log::info('fails in UserController/edit ' . json_encode($e));
                return (new UserResource(null))->additional([
                    'error' => 'User updating failed!',
                ])->response()->setStatusCode(500);
            }
        }
        return (new UserResource(null))->additional([
            'error' => 'User not found!',
        ])->response()->setStatusCode(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param id $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if ($user != null) {
            $user->is_deleted = 1;
            if (substr($user->email, 0, 1) != '_') {
                $user->email = '_' . $user->email;
            }
            try {
                $user->save();
                return (new UserResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('fails in UserController/destroy ' . json_encode($e));
                return (new UserResource(null))->additional([
                    'error' => 'User deleting failed!',
                ])->response()->setStatusCode(500);
            }
        }
        return (new UserResource(null))->additional([
            'error' => 'User not found!',
        ])->response()->setStatusCode(404);
    }
    /**
     * Set user avatar
     *
     * @param int $id
     * @param App\Http\Requests\UserSetAvatarRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setAvatar(UserSetAvatarRequest $request, $id)
    {
        $user = User::where('is_deleted', false)->find($id);
        if ($user != null) {
            $upload_image = new UploadImage;
            $user->avatar_path = $upload_image->getImage($request->file('avatar_path'), 'public/uploads/avatars');
            try {
                $user->save();
                return (new UserResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(200);
            } catch (Exception $e) {
                Log::info("fails in saving image set avater in UserController " . json_encode($e));
                if (env('APP_ENV') == "development") {
                    return (new UserResource(null))->additional([
                        'error' => "fails in saving image set avater in UserController " . json_encode($e)
                    ])->response()->setStatusCode(500);
                } elseif (env('APP_ENV') == "production") {
                    return (new UserResource(null))->additional([
                        'error' => "fails in saving image set avater in UserController "
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new UserResource(null))->additional([
            'error' => 'User not found!',
        ])->response()->setStatusCode(404);
    }

    public function bulkDelete(UserBulkDeleteRequest $request)
    {

        $ids = $request->ids;
        User::where('is_deleted', 0)->whereIn('id', $ids)->update(["is_deleted" => 1, "email" => DB::raw("CONCAT('_', email)")]);
        return (new UserResource(null))->additional([
            'error' => null,
        ])->response()->setStatusCode(204);
    }
}
