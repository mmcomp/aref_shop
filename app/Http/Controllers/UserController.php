<?php

namespace App\Http\Controllers;

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'required|string|between:2,100',
            'email' => 'required|string|max:12',
            'password' => 'required_with:password_confirmation|same:password_confirmation|string|min:6',
            'password_confirmation' => 'required|string|min:6',
            'referrer_users_id' => 'required|integer',
            'address' => 'required|min:10|max:1000',
            'postall' => 'required|digits:10',
            'cities_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user = User::where('email',$request->email)->first();
        if($user != null){
            return response()->json([
                'id' => $user->id,
            ], 201);
        }
        return response()->json([
            'message' => 'User not found!'
        ], 400);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'required|string|between:2,100',
            'email' => 'required|string|max:12',
            'password' => 'required_with:password_confirmation|same:password_confirmation|string|min:6',
            'password_confirmation' => 'required|string|min:6',
            'referrer_users_id' => 'required|integer',
            'address' => 'required|min:10|max:1000',
            'postall' => 'required|digits:10',
            'cities_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user = User::where('id',$request->id)->first();
        if($user != null){
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->pass_txt = $request->password;
            $user->referrer_users_id = $request->referrer_users_id;
            $user->adress = $request->address;
            $user->postall = $request->postall;
            $user->cities_id = $request->cities_id;
            try{
                $user->save();
                return response()->json([
                    'message' => 'User updated successfully!'
                ], 200);
            }catch(Exception $e){
                Log::info('fails in UserController/edit '.$e);
            }
        }
        return response()->json([
            'message' => 'User not found!'
        ], 400);
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user = User::findOrFail($request->id);
        if($user != null){
            $user->delete();
            return response()->json([
                'message' => 'User deleted successfully!'
            ], 200);
        }
        return response()->json([
            'message' => 'Deleting user failed!'
        ], 200);
    }
}
