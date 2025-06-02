<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Kavenegar;

class ReadingStationSms {
    // private $username ;
    // private $password;
    // private $line;
    // private $address;

    private $apiKey;
    private $template;
    private $baseUrl;

    public function __construct() 
    {
        // $this->username = env("SABA_USERNAME");
        // $this->password = env("SABA_PASSWORD");
        // $this->line = env("SABA_LINE");
        // $this->address = env("SABA_ADDRESS");

        // $this->apiKey = env('KAVENEGAR_API_KEY');
        $this->template = env('KAVENEGAR_TEMPLATE');
        // $this->baseUrl = "https://api.kavenegar.com/v1/$this->apiKey/verify/lookup.json";
    }


    public function send(string $number, array $messages)
    {
        try{
            $receptor = $number;
            $token = $messages[0];
            $token2 = $messages[1];
            $token3 = $messages[2];
            $template = $this->template;
            //Send null for tokens not defined in the template
            //Pass token10 and token20 as parameter 6th and 7th
            $result = Kavenegar::VerifyLookup($receptor, $token, $token2, $token3, $template, $type = null);
            // if($result){
            //     foreach($result as $r){
            //         echo "messageid = $r->messageid";
            //         echo "message = $r->message";
            //         echo "status = $r->status";
            //         echo "statustext = $r->statustext";
            //         echo "sender = $r->sender";
            //         echo "receptor = $r->receptor";
            //         echo "date = $r->date";
            //         echo "cost = $r->cost";
            //     }
            // }
            return $result;
        }
        catch(\Kavenegar\Exceptions\ApiException $e){
            // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
            return $e->errorMessage();
        }
        catch(\Kavenegar\Exceptions\HttpException $e){
            // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
            return $e->errorMessage();
        }



    //     $ch = curl_init();
    //     $url = $this->baseUrl."?receptor=$number&token=$messages[0]&token2=$messages[1]&token3=$messages[2]&template=$this->template";
    //     curl_setopt($ch, CURLOPT_URL,$url);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     //curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    //     $result = curl_exec($ch);
    //     curl_close($ch);     
    //     dd($result);   
    //     return json_decode($result);
    }

//     public function send(string $number, array $messages) {
//         if (count($messages) === 0) {
//             throw new HttpException(400, 'You need to pass atleast one message!');
//         }
// $enter = "
// ";
//         if (trim(end($messages)) !== 'لغو۱۱') {
//             $messages[] = 'لغو۱۱';
//         }

//         $parameters = [
//             'username' => $this->username,
//             'password' => $this->password,
//             'line' => $this->line,
//             'life_time' => '60',
//             'mobile' => $number,
//             'message' => implode($enter, $messages),
//         ];

//         $response =  Http::get($this->address, $parameters);

//         return [
//             "status" => $response->status() === 200 && $response['status'] === -1,
//         ];
//     }
}
