<?php
namespace App\Utils;

class Sms {
    public function __construct() {
        $api_key = "484A30787835725542714A394B6979586242372F654D454E4B524F39676D31385673684C52724C344C5A513D";
        $this->url = "https://api.kavenegar.com/v1/$api_key/verify/lookup.json";
        $this->smsUrl="https://api.kavenegar.com/v1/$api_key/sms/send.json";
    }

    public function sendCode($receptor, $token, $template='setavin') {
        $ch = curl_init();
        $url = $this->url."?receptor=$receptor&token=$token&template=$template";
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $result = curl_exec($ch);
        curl_close($ch);        
        return json_decode($result);
    }
}
