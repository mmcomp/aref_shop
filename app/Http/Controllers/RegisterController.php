<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Sms_validation;
use App\Province;
use App\User;
use App\Marketer;
use App\Group;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function index(Request $request){
        if(Auth::check()){
            return view('layouts.register', [
                "error" => "قبلا ثبت نام کرده اید",
                'first_step'=>1
            ]);
        }
        $provinces = Province::pluck('name', 'id');
        return view('layouts.register', ['provinces'=>$provinces,'first_step'=>1 ]);
    }
    public function sendsms(Request $request){
        if ($request->getMethod() == 'GET') {
            return view('layouts.register', [
                "error" => "اطلاعات صحیح ارسال نشده است"
            ]);
        }
        $request->validate([
            'mobile' => 'required|min:11|max:11',
            'fname' => 'required|max:100',
            'lname' => 'required|max:100',
            'province' => 'required',
            'city' => 'required'
        ]);
        $userCount = User::where('email', $request->input('mobile'))->count();
        if ($userCount > 0) {
            $provinces = Province::pluck('name', 'id');
            return view('layouts.register', [
                'provinces'=>$provinces ,
                "error" => "تلفن همراه {$request->input('mobile')} قبلا ثبت نام شده است" ,
                'first_step'=>1
                ]);
        }
        $sms = new Sms_validation;
        $sms->mobile = $request->input('mobile');
        $sms_code = rand(1000, 9999);
        $sms->sms_code = $sms_code;
        $user_info = [
            'fname' => $request->input('fname'),
            'lname' => $request->input('lname'),
            'province' => $request->input('province'),
            'city' => $request->input('city'),
            'mobile' => $request->input('mobile')
        ];
        $sms->user_info = json_encode($user_info, JSON_UNESCAPED_UNICODE);
        Sms_validation::where('mobile', $sms->mobile)->delete();
        $sms->save();
        $this->sendSmsCode($sms->mobile,$sms_code);
        $smsMessage = "لطفا کد پیامک شده را وارد نمایید";
        return view('layouts.register',
            [
            'smsMessage'=>$smsMessage ,
            'provinces'=>[] ,
            'mobile'=>$sms->mobile,
            'second_step'=>1
            ]);
    }
    public function checksms(Request $request){
        if($request->getMethod()=='GET'){
            return view('layouts.register', [
                "error" => "اطلاعات صحیح ارسال نشده است",
                'second_step'=>1
            ]);
        }
        $request->validate([
            'sms_code' => 'required|min:4|max:4'
        ]);
        $res = Sms_validation::where('mobile',$request->input('mobile'))->where('sms_code',$request->input('sms_code'))->count();
        if($res<=0){
            return view('layouts.register',
            [
                'smsMessage'=>'کد وارد شده صحیح نیست' ,
                'provinces'=>[] ,
                'mobile'=>$request->input('mobile'),
                'second_step'=>1
            ]);
        }
        $smsMessage = "جهت ورود به سیستم در آینده لطفا رمز عبور خود را تعیین کنید ";
        return view('layouts.register',
            [
                'smsMessage'=>$smsMessage ,
                'provinces'=>[] ,
                'mobile'=>$request->input('mobile'),
                'sms_code'=>$request->input('sms_code'),
                'third_step'=>1
            ]);
    }
    public function createuser(Request $request){
        if($request->getMethod()=='GET'){
            return view('layouts.register', [
                "error" => "اطلاعات صحیح ارسال نشده است",
                'provinces'=>[] ,
                'first_step'=>1
            ]);
        }

        $userCount = User::where('email', $request->input('mobile'))->count();
        if ($userCount > 0) {
            $provinces = Province::pluck('name', 'id');
            return view('layouts.register', [
                'provinces'=>$provinces ,
                "error" => "تلفن همراه {$request->input('mobile')} قبلا ثبت نام شده است" ,
                'first_step'=>1
                ]);
        }
        $res = Sms_validation::where('mobile', $request->input('mobile'))->where('sms_code', $request->input('sms_code'))->first();
        $user = new User;
        $userInfo = json_decode($res->user_info);
        $user->email = $userInfo->mobile;
        $user->password = Hash::make($request->input('password'));
        $user->first_name = $userInfo->fname;
        $user->last_name = $userInfo->lname;
        $group = Group::select('id')->where('name','Marketer')->first();
        $user->groups_id = $group->id;
        $user->save();
        $marketer  = new Marketer;
        $marketer->users_id = $user->id;
        $marketer->first_name = $userInfo->fname;
        $marketer->last_name = $userInfo->lname;
        $marketer->cell_phone = $userInfo->mobile;
        $marketer->provinces_id = $userInfo->province;
        $marketer->city = $userInfo->city;
        $marketer->save();
        Sms_validation::where('mobile',$userInfo->mobile)->delete();
        return view(
            'layouts.register',
            [
                'smsMessage' => 'ثبت نام با موفقیت انجام شد لطفا کمی صبر کنید',
                'provinces'=>[],
                'final_step' => 1
            ]
        );
    }
    public function sendSmsCode($receptor,$token){
        $api_key = "553133726A6962423652346246504B544C72766668784A6E384C61682B4D565349756B2B374D374C4A6D553D";
        $url = "https://api.kavenegar.com/v1/$api_key/verify/lookup.json";
        $ch = curl_init();
        $url = $url."?receptor=$receptor&token=$token&template=aref";
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result);
    }
}
