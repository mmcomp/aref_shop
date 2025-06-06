<?php
namespace App\Utils;

use Illuminate\Support\Facades\Log;

class Sms {
    public function __construct() {
        $api_key = "553133726A6962423652346246504B544C72766668784A6E384C61682B4D565349756B2B374D374C4A6D553D";
        $this->url = "https://api.kavenegar.com/v1/$api_key/verify/lookup.json";
        $this->smsUrl="https://api.kavenegar.com/v1/$api_key/sms/send.json";
    }

    public function sendCode($receptor, $token, $token2 = '', $token3 = '', $template = 'default') {
        if ($template == 'default') {
            $template = env('KAVENEGAR_DEFAULT');
        }
        $ch = curl_init();
        $url = $this->url."?receptor=$receptor&token=$token&token2=$token2&token3=$token3&template=$template";
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
