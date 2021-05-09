<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Http\Requests\VerifyForgetPasswordRequest;
use App\Http\Requests\VerifyRegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use App\Jobs\SynchronizeUsersWithCrmJob;
use App\Models\SmsValidation;
use App\Models\User;
use App\Utils\Sms;
use Carbon\Carbon;
use Exception;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verify', 'forgetPassword', 'verifyForgetPassword']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        if (!$token = auth('api')->attempt($validated)) {

            return (new UserResource(null))->additional([
                'error' => 'Unauthorized',
            ])->response()->setStatusCode(401);
        }

        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $userData = array_merge(
            $validated,
            ['password' => bcrypt($request->password)]
        );
        $userData["avatar_path"] = "";
        $userData["referrer_users_id"] = 0;
        $userData["pass_txt"] = $request->password;
        $userData["address"] = "";
        $userData["postall"] = "";
        $userData["cities_id"] = 0;
        $userData["groups_id"] = 2;

        $code = rand(1000, 9999);
        SmsValidation::updateOrCreate(
            [
                "mobile" => $userData["email"],
                "type" => "register",
            ],
            [
                "mobile" => $userData["email"],
                "code" => $code,
                "user_info" => json_encode($userData, JSON_UNESCAPED_UNICODE),
                "type" => "register",
            ]
        );
        $sms = new Sms;
        $sms->sendCode($userData["email"], $code);
        return (new UserResource(null))->additional([
            'error' => null,
        ])->response()->setStatusCode(201);

    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(VerifyRegisterRequest $request)
    {

        $smsValidation = SmsValidation::where("mobile", $request->input("email"))->first();
        if ($smsValidation->code !== $request->input("otp")) {
            return (new UserResource(null))->additional([
                'error' => 'OTP is incorrect!',
            ])->response()->setStatusCode(406);
        }

        $smsValidation->delete();

        $userData = json_decode($smsValidation->user_info, true);
        $user = User::create($userData);
        SynchronizeUsersWithCrmJob::dispatch($user)->delay(Carbon::now()->addSecond(env('CRM_ADD_STUDENT_TIMEOUT')));

        $token = auth('api')->login($user);
        return $this->createNewToken($token);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return (new UserResource(null))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);

    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth('api')->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {

        return (new UserResource(auth('api')->user()))->additional([
            'error' => null,
        ]);

    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'error' => null,
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'menus' => auth('api')->getUser()->menus(),
                'group' => auth('api')->getUser()->group,
                'first_name' => auth('api')->getUser()->first_name,
                'last_name' => auth('api')->getUser()->last_name,
                'phone' => auth('api')->getUser()->email,
            ],

        ]);
    }
    /**
     *
     * Forget password
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgetPassword(ForgetPasswordRequest $request)
    {

        $userData = $request->validated();

        $code = rand(1000, 9999);
        SmsValidation::updateOrCreate(
            [
                "mobile" => $userData["email"],
                "type" => "forget_pass",
            ],
            [
                "mobile" => $userData["email"],
                "code" => $code,
                "user_info" => json_encode($userData, JSON_UNESCAPED_UNICODE),
                "type" => "forget_pass",
            ]
        );
        $sms = new Sms;
        $found = User::where('is_deleted', false)->where('email', $userData['email'])->first();
        if ($found != null) {
            $sms->sendCode($userData["email"], $code);
        }
        return (new UserResource(null))->additional([
            'error' => null,
        ])->response()->setStatusCode(200);

    }
    /**
     *
     * Verify forget password
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyForgetPassword(VerifyForgetPasswordRequest $request)
    {

        $smsValidation = SmsValidation::where("mobile", $request->input("email"))->first();
        if ($smsValidation->code !== $request->input("otp")) {
            return (new UserResource(null))->additional([
                'error' => 'OTP is incorrect!',
            ])->response()->setStatusCode(406);
        }
        $smsValidation->delete();
        $user = User::where('is_deleted', false)->where('email', $request->input("email"))->first();
        if ($user != null) {
            $user->password = bcrypt($request->password);
            $user->pass_txt = $request->password;
            try {
                $user->save();
                return (new UserResource(null))->additional([
                    'error' => null,
                ])->response()->setStatusCode(200);
            } catch (Exception $e) {
                Log::info('fails in AuthController/verifyForgetPassword ' . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new UserResource(null))->additional([
                        'error' => 'fails in AuthController/verifyForgetPassword ' . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new UserResource(null))->additional([
                        'error' => 'fails in AuthController/verifyForgetPassword',
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new UserResource(null))->additional([
            'error' => 'User not found!',
        ])->response()->setStatusCode(404);
    }
    /**
     * RESET password api
     *
     * @param  \App\Http\Requests\User\ResetPasswordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request)
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
                Log::info('fails in User/AuthController/resetPassword ' . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new UserResource(null))->additional([
                        'error' => 'fails in User/AuthController/resetPassword  ' . json_encode($e),
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new UserResource(null))->additional([
                        'error' => 'fails in User/AuthController/resetPassword ',
                    ])->response()->setStatusCode(500);
                }
            }

        }
        return (new UserResource(null))->additional([
            'error' => 'You Entered your current password wrong',
        ])->response()->setStatusCode(406);
    }
}
