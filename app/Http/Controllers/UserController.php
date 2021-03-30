<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserEditRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Exception;
use Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'page' => 'required|integer',
            'page_count' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $users = User::all();
        $count = User::count();
        return response()->json([
            'list' => $users,
            'user_counts' => $count
        ], 201);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param App\Http\Requests\UserCreateRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(UserCreateRequest $request)
    {

        $user = new User;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->password_confirmation = $user->password;
        $user->pass_txt = $request->password;
        $user->referrer_users_id = $request->referrer_users_id;
        $user->adress = $request->address;
        $user->postall = $request->postall;
        $user->groups_id = 2;
        $user->cities_id = $request->cities_id;
        $user->save();
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
            $user->adress = $request->address;
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
            return response()->json([
                'message' => 'User deleted successfully!'
            ], 200);
        }
        return response()->json([
            'message' => 'Deleting user failed!'
        ], 200);
    }
}
