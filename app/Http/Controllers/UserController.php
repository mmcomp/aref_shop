<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlockAUserRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\UserBulkDeleteRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserEditRequest;
use App\Http\Requests\UserIndexRequest;
use App\Http\Requests\UserSetAvatarRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Hash;
use App\Utils\UploadImage;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SynchronizeUsersWithCrmJob;
use Carbon\Carbon;
use Log;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\UserIndexRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(UserIndexRequest $request)
    {
        $sort = "id";
        $sort_dir = "desc";
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sort_dir = $request->get('sort_dir');
        }
        if ($request->get('per_page') == "all") {
            $paginated_users = User::where('is_deleted', false)->orderBy($sort, $sort_dir)->get();

        } else {
            $paginated_users = User::where('is_deleted', false)->orderBy($sort, $sort_dir)->paginate(env('PAGE_COUNT'));
        }
        return (new UserCollection($paginated_users))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
    /**
     * get User Id and display all his/her properties
     *
     * @param  id $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {

        $user = User::where('is_deleted', false)->find($id);
        if ($user != null) {
            return (new UserResource($user))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
        } else {
            return (new UserResource($user))->additional([
                'errors' => ['user' => ['User not found!']],
            ])->response()->setStatusCode(404);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param App\Http\Requests\UserCreateRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserCreateRequest $request)
    {

        $saver_users_id = Auth::user()->id;
        $userData = array_merge($request->validated(), ['pass_txt' => $request->password, 'password' => bcrypt($request->password), 'groups_id' => 2, 'avatar_path' => "", 'saver_users_id' => $saver_users_id]);

        $user = User::create($userData);
        SynchronizeUsersWithCrmJob::dispatch($user)->delay(Carbon::now()->addSecond(env('CRM_ADD_STUDENT_TIMEOUT')));
        return (new UserResource($user))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\UserEditRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserEditRequest $request)
    {

        $user = User::where('id', $request->id)->first();
        if ($user != null) {
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            if ($request->password) {
                $user->password = bcrypt($request->password);
                $user->pass_txt = $request->password;
            }
            $user->referrer_users_id = $request->referrer_users_id;
            $user->address = $request->address;
            $user->postall = $request->postall;
            $user->cities_id = $request->cities_id;
            $user->groups_id = $request->groups_id;
            try {
                $user->save();
                return (new UserResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(200);
            } catch (Exception $e) {
                Log::info('fails in UserController/edit ' . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new UserResource(null))->additional([
                        'errors' => ['fail' => ['User updating failed! ' . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new UserResource(null))->additional([
                        'errors' => ['fail' => ['User updating failed!']],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new UserResource(null))->additional([
            'errors' => ['user' => ['User not found!']],
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
        $user = User::where('is_deleted', false)->find($id);
        if ($user != null) {
            $user->is_deleted = 1;
            if (substr($user->email, 0, 1) != '_') {
                $user->email = '_' . $user->email;
            }
            try {
                $user->save();
                return (new UserResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(204);
            } catch (Exception $e) {
                Log::info('fails in UserController/destroy ' . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new UserResource(null))->additional([
                        'errors' =>["fail" => [ 'User deleting failed!' . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new UserResource(null))->additional([
                        'errors' => ['fail' => ['User deleting failed!']],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new UserResource(null))->additional([
            'errors' => ['user' => ['User not found!']],
        ])->response()->setStatusCode(404);
    }

    /* Set user avatar
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
            $upload_image->imageNullablility($user->avatar_path);
            $user->avatar_path = $upload_image->getImage($request->file('avatar_path'), 'public/uploads/avatars');
            try {
                $user->save();
                return (new UserResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(201);
            } catch (Exception $e) {
                Log::info("fails in saving image set avater in UserController " . json_encode($e));
                if (env('APP_ENV') == "development") {
                    return (new UserResource(null))->additional([
                        'errors' => ["fail" => ["fails in saving image set avater in UserController " . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } elseif (env('APP_ENV') == "production") {
                    return (new UserResource(null))->additional([
                        'errors' => ["fail" => ["fails in saving image set avater in UserController "]],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new UserResource(null))->additional([
            'errors' => ['user' => ['User not found!']],
        ])->response()->setStatusCode(404);
    }
    /* Delete user avatar
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAvatar($id)
    {

        $user = User::where('is_deleted', false)->find($id);
        if ($user != null) {
            $avatar = str_replace("storage", "public", $user->avatar_path);
            $user->avatar_path = null;
            if (Storage::exists($avatar)) {
                Storage::delete($avatar);
                try {
                    $user->save();
                    return (new UserResource(null))->additional([
                        'errors' => null,
                    ])->response()->setStatusCode(204);
                } catch (Exception $e) {
                    Log::info("fails in delete avater in UserController " . json_encode($e));
                    if (env('APP_ENV') == "development") {
                        return (new UserResource(null))->additional([
                            'errors' => ["fail" => ["fails in delete avater in UserController " . json_encode($e)]],
                        ])->response()->setStatusCode(500);
                    } elseif (env('APP_ENV') == "production") {
                        return (new UserResource(null))->additional([
                            'errors' => ["fail" => ["fails in delete avater in UserController "]],
                        ])->response()->setStatusCode(500);
                    }
                }
            }
        }
    }
    /**
     * Remove some specified resources from storage.
     *
     * @param  App\Http\Requests\UserBulkDeleteRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(UserBulkDeleteRequest $request)
    {

        $ids = $request->ids;
        User::where('is_deleted', false)->whereIn('id', $ids)->update(["is_deleted" => 1, "email" => DB::raw("CONCAT('_', email)")]);
        return (new UserResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(204);
    }
    /**
     * Search users according to name,last_name,phone
     *
     * @param  \Illuminate\Http\SearchRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(SearchRequest $request)
    {

        $sort = "id";
        $sort_dir = "desc";
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sort_dir = $request->get('sort_dir');
        }
        $phone = trim(request()->email);
        $fullName = trim(request()->name);
        $users_builder = User::where('is_deleted', false)
            ->where(function ($query) use ($phone) {
                if ($phone != null) {
                    $query->where('email', 'like', '%' . $phone . '%');
                }
            })->where(function ($query) use ($fullName) {
            if ($fullName != null) {
                $query->where(DB::raw("CONCAT(IFNULL(first_name, ''), IFNULL(CONCAT(' ', last_name), ''))"), 'like', '%' . $fullName . '%');
            }
        });
        if ($request->per_page == "all") {
            $users = $users_builder->orderBy($sort, $sort_dir)->get();
        } else {
            $users = $users_builder->orderBy($sort, $sort_dir)->paginate(env('PAGE_COUNT'));
        }
        return (new UserCollection($users))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
    /**
     * block a user for chats
     *
     * @param  \Illuminate\Http\BlockAUserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function block(BlockAUserRequest $request)
    // {
        
    //     $users_id = $request->input('users_id');
    //     $value = Redis::get('block_user_'. $users_id);
    //     if($value != null) {
    //         Redis::setex('block_user_'.$users_id, 10800, "block");
    //     }
    //     return (new UserResource(null))->additional([
    //         'errors' => null,
    //     ])->response()->setStatusCode(200);
    // }
    public function userBlock(int $userId)
    {        
       $now=now()->format('Y-m-d H:i:s');       
       $blocketTime=Carbon::parse(now()->addHours(env("REDIS_USER_BLOCK_TIME")))->format('Y-m-d H:i:s');          
        $user=User::find($userId);        
        if( $user)
        {
            $user->blocked=Carbon::parse(now()->addHours(env("REDIS_USER_BLOCK_TIME")))->format('Y-m-d H:i:s');
            if($user->update())
            {
               $blockeUsers=User::where("blocked",">", $now)->pluck("id");
               $blocketUsers["blocked_users"]=$blockeUsers->toArray();
               //$redis->set('blockedUser',json_encode($blocketUser));
              if($this->putBlockedUserToRedis($blocketUsers["blocked_users"]))
              {
                return $blocketUsers;
              }                
            }
            return "";
        }
        else
        {
            $this->errorHandle("User", "کاربر معتبر نمی باشد.");
        }
    }
    public function putBlockedUserToRedis($blocketUsers)
    {
       
        Redis::del('blocked_users');
        $redis = Redis::connection();
       // $redis->hSet('blockedUser',json_encode($blocketUsers),"blocked");
        $redis->set('blocked_users',json_encode($blocketUsers));
        //return $blocketUsers;
        // foreach($blocketUsers as $blocketUser)
        // {
        //     //die($blocketUser);
        //     Redis::hSet('blockedUser',$blocketUser, "blocked");
        // }
        return true;
        
    }
    public function userUnblock(int $userId)
    {
        $user=User::find($userId);
        //dd($user);
        if( $user)
        {
            $user->blocked=NULL;
            $user->update();
            return "";
        }
        else
        {
            $this->errorHandle("User", "کاربر معتبر نمی باشد.");
        }
    }
    public function errorHandle($class, $error)
    {
        throw new HttpResponseException(
            response()->json([
                'errors' => ["$class" => ["$error"]],

            ], 422)
        );
    }
}
