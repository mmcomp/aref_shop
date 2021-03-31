<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserEditRequest;
use App\Http\Requests\UserIndexRequest;
use App\Models\User;
use Exception;
use Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param   App\Http\Requests\UserIndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(UserIndexRequest $request)
    {

        $not_deleted_users = User::where('is_deleted', 0);
        $paginated_users = $not_deleted_users->paginate($request->page_count);
        $allUsers = $not_deleted_users->get();
        $count = $not_deleted_users->count();
        return response()->json([
            'error' => null,
            'data' => [
                'list' => $allUsers,
                'paginated_users' => $paginated_users,
                'user_counts' => $count
            ]
        ], 200);
    }
    /**
     * get User Id and display all his/her properties
     *
     * @param  id $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser($id)
    {

        $user = User::find($id);
        if ($user != null && !$user->is_deleted) {
            return response()->json([
                'error' => null,
                'data' => [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'avatar_path' => $user->avatar_path,
                    'referrer_users_id' => $user->referrer_users_id,
                    'address' => $user->address,
                    'postall' => $user->postall,
                    'cities_id' => $user->cities_id,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'groups_id' => $user->groups_id
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => 'User not found',
                'data' => null
            ], 404);
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

        $userData = array_merge($request->validated(), ['pass_txt' => $request->password, 'groups_id' => 2, 'avatar_path' => ""]);
        $user = User::create($userData);
        return response()->json([
            'error' => null,
            'data' => [
                'id' => $user->id
            ]
        ], 201);
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
            $user->password = bcrypt($request->password);
            $user->pass_txt = $request->password;
            $user->referrer_users_id = $request->referrer_users_id;
            $user->address = $request->address;
            $user->postall = $request->postall;
            $user->cities_id = $request->cities_id;
            try {
                $user->save();
                return response()->json([
                    'error' => null,
                    'data'  => null
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'error' => 'User updating failed!',
                    'data'  => null
                ], 500);
                Log::info('fails in UserController/edit ' . json_encode($e));
            }
        }
        return response()->json([
            'error' => 'User not found!',
            'data'  =>  null
        ], 404);
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
            $user->email = '_' . $user->email;
            try {
                $user->save();
                return response()->json([
                    'error' => null,
                    'data'  => null
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'error' => 'User deleting failed!',
                    'data' => null
                ], 500);
                Log::info('fails in UserController/destroy ' . json_encode($e));
            }
        }
        return response()->json([
            'error' => 'User not found!',
            'data'  => null
        ], 404);
    }
}
