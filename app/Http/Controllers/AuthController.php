<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyForgetPasswordRequest;
use App\Http\Requests\VerifyRegisterRequest;
use App\Models\User;
use App\Models\SmsValidation;
use App\Utils\Sms;
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
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verify','forgetPassword','verifyForgetPassword']]);
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

            return response()->json(['error' => 'Unauthorized','data' => null], 401);
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
        $userData["adress"] = "";
        $userData["postall"] = "";
        $userData["cities_id"] = 0;
        $userData["groups_id"] = 2;

        $code = rand(1000, 9999);
        SmsValidation::updateOrCreate(
            [
                "mobile" => $userData["email"],
                "type" => "register"
            ],
            [
                "mobile" => $userData["email"],
                "code" => $code,
                "user_info" => json_encode($userData, JSON_UNESCAPED_UNICODE),
                "type" => "register"
            ]
        );
        $sms = new Sms;
        $sms->sendCode($userData["email"], $code);
        return response()->json([
            'error' => null,
            'data'  => null
        ], 201);

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
            return response()->json(['error' => 'OTP is incorrect!','data' => null], 406);
        }

        $smsValidation->delete();

        $userData = json_decode($smsValidation->user_info, true);
        $user = User::create($userData);

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

        return response()->json(['error' => null, 'data' => null],200);
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
        return response()->json(auth('api')->user());
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
            'data'  => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'menus' => auth('api')->getUser()->menus()
            ]

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
                "type" => "forget_pass"
            ],
            [
                "mobile" => $userData["email"],
                "code" => $code,
                "user_info" => json_encode($userData, JSON_UNESCAPED_UNICODE),
                "type" => "forget_pass"
            ]
        );
        $sms = new Sms;
        $found = User::where('is_deleted',false)->where('email',$userData['email'])->first();
        if($found != null){
            $sms->sendCode($userData["email"], $code);
            return response()->json([
                'error' => null,
                'data'  => null
            ], 200);
        }
        return response()->json([
            'error' => 'User not found!',
            'data'  => null
        ], 404);
       
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
            return response()->json(['error' => 'OTP is incorrect!','data' => null], 406);
        }
        $smsValidation->delete();
        $user = User::where('is_deleted',false)->where('email', $request->input("email"))->first();
        if ($user != null) {
            $user->password = bcrypt($request->password);
            $user->pass_txt = $request->password;
            try {
                $user->save();
                return response()->json([
                    'error' => null,
                    'data'  => null
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'error' => 'fails in AuthController/verifyForgetPassword',
                    'data'  => null
                ], 500);
                Log::info('fails in AuthController/verifyForgetPassword ' . json_encode($e));

            }
        }
        return response()->json([
            'error' => 'User not found!',
            'data'  => null
        ], 404);
    }
}
