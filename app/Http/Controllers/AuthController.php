<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\SmsValidation;
use App\Utils\Sms;
use Validator;
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
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verify']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|min:10',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = auth('api')->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'required|string|between:2,100',
            'email' => 'required|string|max:12|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $userData = array_merge(
            $validator->validated(),
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
            'message' => 'User successfully registered'
        ], 201);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|integer|min:1000',
            'email' => 'required|string|max:12|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $smsValidation = SmsValidation::where("mobile", $request->input("email"))->first();
        if ($smsValidation->code !== $request->input("otp")) {
            return response()->json(['error' => 'OTP is incorrect!'], 406);
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

        return response()->json(['message' => 'User successfully signed out']);
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
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'menus' => auth('api')->getUser()->menus()
        ]);
    }
    /**
     *
     * Forget password
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:12'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $userData = $validator->validated();

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
        $sms->sendCode($userData["email"], $code);
        return response()->json([
            'message' => 'Getting mobile of user and sending Sms forget password is successfully done!'
        ], 201);
    }
    /**
     *
     * Verify forget password
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyForgetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'otp' => 'required|integer|min:1000',
            'email' => 'required|string|max:12',
            'password' => 'required_with:password_confirmation|same:password_confirmation|string|min:6',
            'password_confirmation' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $smsValidation = SmsValidation::where("mobile", $request->input("email"))->first();
        if ($smsValidation->code !== $request->input("otp")) {
            return response()->json(['error' => 'OTP is incorrect!'], 406);
        }
        $smsValidation->delete();
        $user = User::where('email', $request->input("email"))->first();
        if ($user != null) {
            $user->password = bcrypt($request->password);
            $user->pass_txt = $request->password;
            try {
                $user->save();
                return response()->json([
                    'message' => 'Verfying forget password is successfully done!'
                ], 201);
            } catch (Exception $e) {
                Log::info('fails in AuthController/verifyForgetPassword ' . $e);
            }
        }
        return response()->json([
            'message' => 'User not found!'
        ], 400);
    }
}
