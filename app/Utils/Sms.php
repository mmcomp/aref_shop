<?php
namespace App\Utils;

class Sms {
    public function __construct() {
        $api_key = "553133726A6962423652346246504B544C72766668784A6E384C61682B4D565349756B2B374D374C4A6D553D";
        $this->url = "https://api.kavenegar.com/v1/$api_key/verify/lookup.json";
        $this->smsUrl="https://api.kavenegar.com/v1/$api_key/sms/send.json";
    }

    public function sendCode($receptor, $token, $template='aref') {
        $ch = curl_init();
        $url = $this->url."?receptor=$receptor&token=$token&template=$template";
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $result = curl_exec($ch);
        dd('1');
        curl_close($ch);
        return json_decode($result);
    }
}
