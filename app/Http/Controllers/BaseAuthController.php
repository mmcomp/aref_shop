<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\RegisterLoginRequest;
use App\Http\Requests\RegisterWithOPTRequest;
use App\Http\Requests\VerifyForgetPasswordRequest;
use App\Http\Requests\VerifyRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\SmsValidation;
use App\Models\User;
use App\Utils\Sms;
use App\Jobs\SynchronizeUsersWithCrmJob;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Hautelook\Phpass\PasswordHash;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use MikeMcLin\WpPassword\WpPassword;

class BaseAuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verify', 'forgetPassword', 'verifyForgetPassword', 'registerLogin', 'registerWithOTP', 'loginWithOTP']]);
    }

    public function check($value, $hashedValue, array $options = [])
    {
        if (Hash::needsRehash($hashedValue)) {
            $p = new PasswordHash(null, null);
            $wpPassword = new WpPassword($p);
            if ($wpPassword->check($value, $hashedValue)) {
                $newHashedValue = (new \Illuminate\Hashing\BcryptHasher)->make($value, $options);
                \Illuminate\Support\Facades\DB::update('UPDATE users SET `password` = "' . $newHashedValue . '", `pass_txt` = "' . $value . '" WHERE `password` = "' . $hashedValue . '"');
                $hashedValue = $newHashedValue;
            }
        }
    }

    public function userToRedis($user, string $token)
    {
        Log::info("Start Redis : " . json_encode($user));
        $value = Redis::hGet('user', $user->id);
        // if($value != $token) {
        //    $res = Redis::publish('node', json_encode([
        //     'id' => $user->id,
        //     'old_token' => $value,
        //     'new_token' => $token
        //    ]));
        //    Log::info("Pub : " . json_encode($res));
        // }
        Redis::hSet('user', $user->id, $token);
        $first_name = $user->first_name == null ? '' : $user->first_name;
        $last_name = $user->last_name == null ? '' : $user->last_name;
        Redis::hSet('name', $user->id, $first_name . ' ' . $last_name);
        Redis::hSet('expires_in', $user->id, Carbon::now()->addDays(7));
        Log::info("hSet : " . $token);
        $value = Redis::hGet('user', $user->id);
        Log::info("hGet : " . $value);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {

        $user = User::where('email', $request->input('email'))->first();
        if ($user) {
            $this->check($request->input('password'), $user->password);
        }

        $validated = $request->validated();
        if (!$token = auth('api')->attempt($validated)) {

            return (new UserResource(null))->additional([
                'errors' => ['authentication' => ['Unauthorized']],
            ])->response()->setStatusCode(401);
        }

        $this->userToRedis($user, $token);
        return $this->createNewToken($token);
    }

    public function loginWithOTP(RegisterLoginRequest $request)
    {
        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            return (new UserResource(null))->additional([
                'errors' => ['authentication' => ['Unauthorized']],
            ])->response()->setStatusCode(401);
        }


        $twoMinBefore = date("Y-m-d H:i:s", strtotime("-1 minutes"));
        $smsValidation = SmsValidation::where("mobile", $request->input("email"))->whereType("login")->first();
        if (!$smsValidation) {
            return (new UserResource(null))->additional([
                'errors' => ['OTP' => ['otp does not exist']],
            ])->response()->setStatusCode(406);
        }
        if (strtotime($smsValidation->created_at) <= strtotime($twoMinBefore)) {
            return (new UserResource(null))->additional([
                'errors' => ['OTP' => ['one minutes time out']],
            ])->response()->setStatusCode(406);
        }
        if ($smsValidation->code !== $request->input("otp")) {
            return (new UserResource(null))->additional([
                'errors' => ['OTP' => ['OTP is incorrect!']],
            ])->response()->setStatusCode(406);
        }

        $smsValidation->delete();
        $token = auth('api')->login($user);
        $this->userToRedis($user, $token);
        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerWithOTP(RegisterWithOPTRequest $request)
    {
        $request->validated();
        $twoMinBefore = date("Y-m-d H:i:s", strtotime("-1 minutes"));
        $smsValidation = SmsValidation::where("mobile", $request->input("email"))->whereType("register")->first();
        if (!$smsValidation) {
            return (new UserResource(null))->additional([
                'errors' => ['OTP' => ['otp does not exist']],
            ])->response()->setStatusCode(406);
        }
        if (strtotime($smsValidation->created_at) <= strtotime($twoMinBefore)) {
            return (new UserResource(null))->additional([
                'errors' => ['OTP' => ['you may wait more than one minutes']],
            ])->response()->setStatusCode(406);
        }
        if ($smsValidation->code !== $request->input("otp")) {
            return (new UserResource(null))->additional([
                'errors' => ['OTP' => ['OTP is incorrect!']],
            ])->response()->setStatusCode(406);
        }
        $userData["email"] = $request->email;
        $userData["last_name"] = $request->last_name;
        $userData["first_name"] = "";
        $userData["avatar_path"] = "";
        $userData["referrer_users_id"] = 0;
        $userData["password"] = null;
        $userData["pass_txt"] = null;
        $userData["address"] = "";
        $userData["postall"] = "";
        $userData["cities_id"] = 0;
        $userData["groups_id"] = 2;
        $user = User::create($userData);

        $smsValidation->delete();

        Log::info("Before_Sync".Carbon::now()->addSecond(env('CRM_ADD_STUDENT_TIMEOUT'))->format("YYYY/mm/dd H:i:s") );
        //SynchronizeUsersWithCrmJob::dispatch($user)->delay(Carbon::now()->addSecond(env('CRM_ADD_STUDENT_TIMEOUT')));
        SynchronizeUsersWithCrmJob::dispatch($user)->delay(now());

        $token = auth('api')->login($user);
        $this->userToRedis($user, $token);
        return $this->createNewToken($token);
    }

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
        SmsValidation::where('mobile', $userData["email"])->delete();
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
            'errors' => null,
        ])->response()->setStatusCode(201);
    }
    public function registerLogin(RegisterLoginRequest $request)
    {
        $email = $request->input("email");
        $code = rand(1000, 9999);
        $type = "register";
        if (User::whereEmail($email)->first()) {
            $type  = "login";
        }
        $twoMinBefore = date("Y-m-d H:i:s", strtotime("-1 minutes"));
        $canSendSms = SmsValidation::where('mobile', $email)->first();
        if ($canSendSms && strtotime($canSendSms->created_at) > strtotime($twoMinBefore)) {
            return (new UserResource(null))->additional([
                'errors' => ['soon_request' => ['you may wait more than one minutes']],
            ])->response()->setStatusCode(406);
        }
        SmsValidation::where('mobile', $email)->delete();
        SmsValidation::create(
            [
                "mobile" => $email,
                "code" => $code,
                "user_info" => "{}",
                "type" => $type,
            ]
        );
        $sms = new Sms;
        $sms->sendCode($email, $code);
        return response([
            "data" => ["type" => $type]
        ])->setStatusCode(201);
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
                'errors' => ['OTP' => ['OTP is incorrect!']],
            ])->response()->setStatusCode(406);
        }

        $smsValidation->delete();

        $userData = json_decode($smsValidation->user_info, true);
        $user = User::create($userData);
        SynchronizeUsersWithCrmJob::dispatch($user)->delay(Carbon::now()->addSecond(env('CRM_ADD_STUDENT_TIMEOUT')));

        $token = auth('api')->login($user);
        $this->userToRedis($user, $token);
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
            'errors' => null,
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
            'errors' => null,
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
            'errors' => null,
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 24 * 7,
                'menus' => auth('api')->getUser()->menus(),
                'group' => auth('api')->getUser()->group,
                'user_id' => auth('api')->getUser()->id,
                'first_name'  => auth('api')->getUser()->first_name,
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
        SmsValidation::where('mobile', $userData["email"])->delete();
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
            'errors' => null,
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
                'errors' => ['OTP' => ['OTP is incorrect!']],
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
                    'errors' => null,
                ])->response()->setStatusCode(200);
            } catch (Exception $e) {
                Log::info('fails in AuthController/verifyForgetPassword ' . json_encode($e));
                if (env('APP_ENV') == 'development') {
                    return (new UserResource(null))->additional([
                        'errors' => ['fail' => ['fails in AuthController/verifyForgetPassword ' . json_encode($e)]],
                    ])->response()->setStatusCode(500);
                } else if (env('APP_ENV') == 'production') {
                    return (new UserResource(null))->additional([
                        'errors' => ['fail' => ['fails in AuthController/verifyForgetPassword']],
                    ])->response()->setStatusCode(500);
                }
            }
        }
        return (new UserResource(null))->additional([
            'errors' => ['user' => ['User not found!']],
        ])->response()->setStatusCode(404);
    }
    public function synchronizeUsers()
    {

        $users = User::where('is_deleted', false)->get();
        foreach ($users as $user) {
            SynchronizeUsersWithCrmJob::dispatch($user)->delay(Carbon::now()->addSecond(env('CRM_ADD_STUDENT_TIMEOUT')));
        }
        return (new UserResource(null))->additional([
            'errors' => null,
        ])->response()->setStatusCode(200);
    }
}
