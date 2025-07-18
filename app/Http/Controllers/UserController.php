<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\UserBulkDeleteRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserEditRequest;
use App\Http\Requests\UserFullCreateRequest;
use App\Http\Requests\UserIndexRequest;
use App\Http\Requests\UserSetAvatarRequest;
use App\Http\Resources\GroupCollection;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use App\Utils\UploadImage;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SynchronizeUsersWithCrmJob;
use App\Models\Group;
use App\Utils\Quiz24Service;
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
        $users = User::where('is_deleted', false)->orderBy($sort, $sort_dir);
        if (Auth::user()->group->type == 'school-admin') {
            $users->where('school_id', Auth::user()->school_id);
        }
        if ($request->get('per_page') == "all") {
            $paginated_users = $users->get();
        } else {
            $paginated_users = $users->paginate(env('PAGE_COUNT'));
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
            if (Auth::user()->group->type == 'school-admin') {
                $user->school_id = Auth::user()->school_id;
            }
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
        $user_registered = 2; // student and AREF clients
        $user_type = isset($request->groups_id) ? $request->groups_id : $user_registered;
        $userData = array_merge($request->validated(), ['pass_txt' => $request->password, 'password' => bcrypt($request->password), 'groups_id' => $user_type, 'avatar_path' => "", 'saver_users_id' => $saver_users_id]);
        if ($request->school_id) {
            $userData['school_id'] = $request->school_id;
        }
        if (Auth::user()->group->type == 'school-admin') {
            $userData['school_id'] = Auth::user()->school_id;
        }

        $user = User::create($userData);
        SynchronizeUsersWithCrmJob::dispatch($user)->delay(Carbon::now()->addSecond(env('CRM_ADD_STUDENT_TIMEOUT')));
        return (new UserResource($user))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    function fullStore(UserFullCreateRequest $request)
    {
        $authUser = Auth::user();
        $authGroup = $authUser->group;
        if (in_array($authGroup->type, ['user', 'teacher'])) {
            return (new UserResource(null))->additional([
                'errors' => ['user' => ['Access forbidden!']],
            ])->response()->setStatusCode(403);
        }

        $group = Group::find($request->groups_id);
        switch ($group->type) {
            case 'admin':
            case 'admin_reading_station':
                if ($authGroup->type !== 'admin') {
                    return (new UserResource(null))->additional([
                        'errors' => ['user' => ['This groups id is forbidden!']],
                    ])->response()->setStatusCode(403);
                }
                break;
            case 'admin_reading_station_branch':
                if (!in_array($authGroup->type, ['admin', 'admin_reading_station'])) {
                    return (new UserResource(null))->additional([
                        'errors' => ['user' => ['This groups id is forbidden!']],
                    ])->response()->setStatusCode(403);
                }
                break;
            case 'user_reading_station_branch':
                if (!in_array($authGroup->type, ['admin', 'admin_reading_station', 'admin_reading_station_branch'])) {
                    return (new UserResource(null))->additional([
                        'errors' => ['user' => ['This groups id is forbidden!']],
                    ])->response()->setStatusCode(403);
                }
                if (
                    $authGroup->type === 'admin_reading_station_branch' &&
                    (
                        !$authUser->reading_station_id ||
                        (
                            $authUser->reading_station_id &&
                            $authUser->reading_station_id !== $request->reading_station_id
                        )
                    )
                ) {
                    return (new UserResource(null))->additional([
                        'errors' => ['user' => ['This groups id is forbidden!']],
                    ])->response()->setStatusCode(403);
                }
                break;
            case 'user':
                if (!in_array($authGroup->type, ['admin', 'admin_reading_station', 'admin_reading_station_branch', 'user_reading_station_branch'])) {
                    return (new UserResource(null))->additional([
                        'errors' => ['user' => ['This groups id is forbidden!']],
                    ])->response()->setStatusCode(403);
                }
                break;
            default:
                return (new UserResource(null))->additional([
                    'errors' => ['user' => ['Unrecognizable group!', $group->type]],
                ])->response()->setStatusCode(403);
        }

        $found = User::where("email", $request->email)->first();
        if ($found) {
            return (new UserResource(null))->additional([
                'errors' => ['user' => ['User with this email exists!']],
            ])->response()->setStatusCode(404);
        }
        $found = User::where("national_code", $request->national_code)->first();
        if ($found) {
            return (new UserResource(null))->additional([
                'errors' => ['user' => ['User with this national code exists!']],
            ])->response()->setStatusCode(404);
        }

        $user = new User;
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
        $user->national_code = $request->national_code;
        $user->gender = $request->gender;
        $user->home_tell = $request->home_tell;
        $user->father_cell = $request->father_cell;
        $user->mother_cell = $request->mother_cell;
        $user->grade = $request->grade ?? 1;
        $user->description = $request->description;
        $user->reading_station_id = $request->reading_station_id;
        $user->school = $request->school;
        $user->major = $request->major;
        $user->saver_users_id = Auth::user()->id;
        if (Auth::user()->group->type == 'school-admin') {
            $user->school_id = Auth::user()->school_id;
        } else {
            $user->school_id = $request->school_id;
        }
        $user->save();
        return (new UserResource($user))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
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
        if (Auth::user()->group->type == 'school-admin' && $user->school_id != Auth::user()->school_id) {
            return (new UserResource(null))->additional([
                'errors' => ['user' => ['Access forbidden!']],
            ])->response()->setStatusCode(403);
        }
        if ($user != null) {
            $user->first_name = $request->first_name ?? $user->first_name;
            $user->last_name = $request->last_name ?? $user->last_name;
            // $user->email = $request->email;
            if ($request->password) {
                $user->password = bcrypt($request->password);
                $user->pass_txt = $request->password;
            }
            $user->referrer_users_id = $request->referrer_users_id ?? $user->referrer_users_id;
            $user->address = $request->address ?? $user->address;
            $user->postall = $request->postall ?? $user->postall;
            $user->cities_id = $request->cities_id ?? $user->cities_id;
            $user->groups_id = $request->groups_id ?? $user->groups_id;
            $user->national_code = $request->national_code ?? $user->national_code;
            $user->gender = $request->gender ?? $user->gender;
            $user->home_tell = $request->home_tell ?? $user->home_tell;
            $user->father_cell = $request->father_cell ?? $user->father_cell;
            $user->mother_cell = $request->mother_cell ?? $user->mother_cell;
            $user->grade = $request->grade ?? $user->grade;
            $user->description = $request->description ?? $user->description;
            $user->reading_station_id = $request->reading_station_id ?? $user->reading_station_id;
            $user->disabled = $request->exists('disabled') ? $request->disabled : $user->disabled;
            $user->major = $request->major;
            $user->saver_users_id = Auth::user()->id;
            $user->school_id = $request->school_id ?? $user->school_id;
            if (Auth::user()->group->type == 'school-admin') {
                $user->school_id = Auth::user()->school_id;
            }
            $user->save();
            if ($request->has('first_name') || $request->has('last_name')) {
                Quiz24Service::updateStudent([
                    "userName" => $user->email,
                    "name" => $user->first_name,
                    "family" => $user->last_name,
                ]);
            }
            return (new UserResource(null))->additional([
                'errors' => null,
            ])->response()->setStatusCode(200);
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
        if (Auth::user()->group->type == 'school-admin' && $user->school_id != Auth::user()->school_id) {
            return (new UserResource(null))->additional([
                'errors' => ['user' => ['Access forbidden!']],
            ])->response()->setStatusCode(403);
        }
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
                        'errors' => ["fail" => ['User deleting failed!' . json_encode($e)]],
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
        if (Auth::user()->group->type == 'school-admin' && $user->school_id != Auth::user()->school_id) {
            return (new UserResource(null))->additional([
                'errors' => ['user' => ['Access forbidden!']],
            ])->response()->setStatusCode(403);
        }
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
        if (Auth::user()->group->type == 'school-admin' && $user->school_id != Auth::user()->school_id) {
            return (new UserResource(null))->additional([
                'errors' => ['user' => ['Access forbidden!']],
            ])->response()->setStatusCode(403);
        }
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
        $groupName = trim(request()->group);
        $groupType = trim(request()->group_type);
        $schoolId = trim(request()->school_id);
        if (Auth::user()->group->type == 'school-admin') {
            $schoolId = Auth::user()->school_id;
        }
        $users_builder = User::where('is_deleted', false)
            ->where(function ($query) use ($phone) {
                if ($phone != null) {
                    $query->where('email', 'like', '%' . $phone . '%');
                }
            })->where(function ($query) use ($fullName) {
                if ($fullName != null) {
                    $query->where(DB::raw("CONCAT(IFNULL(first_name, ''), IFNULL(CONCAT(' ', last_name), ''))"), 'like', '%' . $fullName . '%');
                }
            })->whereHas('group', function ($query) use ($groupName, $groupType) {
                if ($groupName != null) {
                    $query->where('name', 'like', '%' . $groupName . '%');
                }
                if ($groupType != null) {
                    $query->where('type', 'like', '%' . $groupType . '%');
                }
            });
        if ($schoolId != null) {
            $users_builder->where('school_id', $schoolId);
        }
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
    public function showAllUserBlock()
    {
        $now = now()->format('Y-m-d H:i:s');
        $users = User::where("blocked", ">=", Carbon::now()->toDateString() . ' 00:00:00')->pluck("id");

        $blockedUsers["blocked_users"] = $users->toArray();
        if ($this->putBlockedUserToRedis($blockedUsers["blocked_users"])) {
            return $blockedUsers;
        }
        $this->errorHandle("User", " به روز رسانی کاربران بلاک شده با مشکل مواجه شد.");
    }
    public function userBlock(int $userId)
    {
        if ($this->isAdmin($userId)) {
            $this->errorHandle("User", "امکان مسدود کردن کاربر دارای دسترسی مدیریت وجود ندارد.");
        }

        $now = now()->format('Y-m-d H:i:s');
        $blocketTime = Carbon::parse(now()->addHours(env("REDIS_USER_BLOCK_TIME")))->format('Y-m-d H:i:s');
        $user = User::find($userId);
        if ($user) {
            $user->blocked = Carbon::parse(now()->addHours(env("REDIS_USER_BLOCK_TIME")))->format('Y-m-d H:i:s');
            if ($user->update()) {
                $users = User::where("blocked", ">=", Carbon::now()->toDateString() . ' 00:00:00')->pluck("id");
                $blockedUsers["blocked_users"] = $users->toArray();
                //$redis->set('blockedUser',json_encode($blocketUser));
                if ($this->putBlockedUserToRedis($blockedUsers["blocked_users"])) {
                    return $blockedUsers;
                }
            }
            return "";
        } else {
            $this->errorHandle("User", "کاربر معتبر نمی باشد.");
        }
    }
    public function userUnblock(int $userId)
    {
        $user = User::find($userId);
        if ($user) {

            $user->blocked = NULL;
            $user->update();
            return $this->showAllUserBlock();
            //return "";
        } else {
            $this->errorHandle("User", "کاربر معتبر نمی باشد.");
        }
    }
    public function isAdmin(int $userId)
    {
        $isAdmin = User::where('id', $userId)->where("groups_id", 1)->first();
        return $isAdmin;
    }
    public function putBlockedUserToRedis($blocketUsers)
    {

        Redis::del('blocked_users');
        $redis = Redis::connection();
        // $redis->hSet('blockedUser',json_encode($blocketUsers),"blocked");
        $redis->set('blocked_users', json_encode($blocketUsers));
        //return $blocketUsers;
        // foreach($blocketUsers as $blocketUser)
        // {
        //
        //     Redis::hSet('blockedUser',$blocketUser, "blocked");
        // }
        return true;
    }
    public function errorHandle($class, $error)
    {
        throw new HttpResponseException(
            response()->json([
                'errors' => ["$class" => ["$error"]],

            ], 422)
        );
    }

    public function groupIndex()
    {
        $authUser = Auth::user();
        $authGroup = $authUser->group;
        $groups = Group::where('type', '!=', 'user');
        switch ($authGroup->type) {
            case 'admin':
                $groups = $groups->get();
                break;
            case 'admin_reading_station':
                $groups = $groups->whereNotIn('type', ['admin', 'teacher', 'admin_reading_station'])->get();
                break;
            case 'admin_reading_station_branch':
                $groups = $groups->whereNotIn('type', ['admin', 'teacher', 'admin_reading_station', 'admin_reading_station_branch'])->get();
                break;
            case 'user_reading_station_branch':
                $groups = $groups->whereNotIn('type', ['admin', 'teacher', 'admin_reading_station', 'admin_reading_station_branch', 'user_reading_station_branch'])->get();
                break;
            default:
                $groups = null;
        }
        return (new GroupCollection($groups))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }

    public function disableUser(User $user)
    {
        $authUser = Auth::user();
        $authGroup = $authUser->group;
        switch ($authGroup->type) {
            case 'admin_reading_station':
                if (!$user->readingStation || $user->group->type === 'admin_reading_station')
                    return (new UserResource(null))->additional([
                        'errors' => ['user' => ['Access denied!']],
                    ])->response()->setStatusCode(403);
                break;
            case 'admin_reading_station_branch':
                if (
                    !$user->readingStation ||
                    $user->group->type !== 'user_reading_station_branch' ||
                    (
                        $user->reading_station_id &&
                        $user->reading_station_id !== $authUser->reading_station_id
                    )
                ) {
                    return (new UserResource(null))->additional([
                        'errors' => ['user' => ['Access denied!']],
                    ])->response()->setStatusCode(403);
                }
                break;
            case 'user_reading_station_branch':
                return (new UserResource(null))->additional([
                    'errors' => ['user' => ['Access denied!']],
                ])->response()->setStatusCode(403);
        }

        $user->disabled = true;
        $user->saver_users_id = Auth::user()->id;
        $user->save();

        return (new UserResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }

    public function enableUser(User $user)
    {
        $authUser = Auth::user();
        $authGroup = $authUser->group;
        switch ($authGroup->type) {
            case 'admin_reading_station':
                if (!$user->readingStation || $user->group->type === 'admin_reading_station')
                    return (new UserResource(null))->additional([
                        'errors' => ['user' => ['Access denied!']],
                    ])->response()->setStatusCode(403);
                break;
            case 'admin_reading_station_branch':
                if (
                    !$user->readingStation ||
                    $user->group->type !== 'user_reading_station_branch' ||
                    (
                        $user->reading_station_id &&
                        $user->reading_station_id !== $authUser->reading_station_id
                    )
                )
                    return (new UserResource(null))->additional([
                        'errors' => ['user' => ['Access denied!']],
                    ])->response()->setStatusCode(403);
                break;
            case 'user_reading_station_branch':
                return (new UserResource(null))->additional([
                    'errors' => ['user' => ['Access denied!']],
                ])->response()->setStatusCode(403);
        }

        $user->disabled = false;
        $user->saver_users_id = Auth::user()->id;
        $user->save();

        return (new UserResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(201);
    }
}
