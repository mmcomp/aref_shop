<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserEditRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Log;

class UserController extends Controller
{
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
            $user->address = $request->address;
            $user->postall = $request->postall;
            $user->cities_id = $request->cities_id;
            try {
                $user->save();
                return (new UserResource(null))->additional([
                    'errors' => null,
                ])->response()->setStatusCode(200);
            } catch (Exception $e) {
                Log::info('fails in User/UserController/edit ' . json_encode($e));
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
}
