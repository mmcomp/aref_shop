<?php
namespace App\Utils;

use Illuminate\Support\Facades\Log;

class Sms {
    public function __construct() {
        $api_key = "432F3752543972436F4A6E4A4F4A4A757663566F6B6645754E79367242672B36506A5A52566945334E45343D";
        $this->url = "https://api.kavenegar.com/v1/$api_key/verify/lookup.json";
        $this->smsUrl="https://api.kavenegar.com/v1/$api_key/sms/send.json";
    }

    public function sendCode($receptor, $token, $template='aref') {
        $ch = curl_init();
        $url = $this->url."?receptor=$receptor&token=$token&template=$template";
        Log::info($url);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $result = curl_exec($ch);
        curl_close($ch);        
        return json_decode($result);
    }
}
