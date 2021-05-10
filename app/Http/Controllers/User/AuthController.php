<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseAuthController;
use App\Http\Requests\User\ChangePasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use Exception;

class AuthController extends BaseAuthController
{
    /**
     * CHANGE password api
     *
     * @param  \App\Http\Requests\User\ChangePasswordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        
        $user = Auth::user();
        if (Hash::check($request->input('current_password'), $user->password)) {
            $user->password = bcrypt($request->input('new_password'));
            $user->pass_txt = $request->input('new_password');
            try {
                $user->save();
                return (new UserResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(200);
            } catch (Exception $e) {
                Log::info('fails in User/AuthController/changePassword ' . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new UserResource(null))->additional([
                        'error' => 'fails in User/AuthController/changePassword  ' . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new UserResource(null))->additional([
                        'error' => 'fails in User/AuthController/changePassword ',
                    ])->response()->setStatusCode(500);
                }
            }

        }
        return (new UserResource(null))->additional([
            'error' => 'You Entered your current password wrong',
        ])->response()->setStatusCode(406);
    }
}
