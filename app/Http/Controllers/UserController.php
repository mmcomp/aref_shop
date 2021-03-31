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

        $paginated_users = User::paginate($request->page_count);
        $allUsers = User::where('is_deleted',0)->get();
        $count = User::count();
        return response()->json([
            'list' => $allUsers,
            'paginated_users' => $paginated_users,
            'user_counts' => $count
        ], 201);
    }
    /**
     * get User Id and display all his/her properties
     *
     * @param  id $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser($id)
    {

        $user = User::findOrFail($id);
        if ($user != null && !$user->is_deleted) {
            return response()->json([
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
            ], 201);
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
        dd($user);
        if ($user != null) {
            return response()->json([
                'id' => $user->id
            ], 201);
        }
        return response()->json(['message' => 'Creating user failed!'], 400);
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
                    'message' => 'User updated successfully!'
                ], 200);
            } catch (Exception $e) {
                Log::info('fails in UserController/edit ' . json_encode($e));
            }
        }
        return response()->json([
            'message' => 'User not found!'
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param id $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user != null) {
            $user->is_deleted = 1;
            $user->email = '_' . $user->email;
            try {
                $user->save();
                return response()->json([
                    'message' => 'User deleted successfully!'
                ], 200);
            } catch (Exception $e) {
                Log::info('fails in UserController/destroy ' . json_encode($e));
            }
        }
        // return response()->json([
        //     'message' => 'Deleting user failed!'
        // ], 200);
    }
}
