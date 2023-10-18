<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReadingStationSms {
    private $username ;
    private $password;
    private $line;
    private $address;

    public function __construct() {
        $this->username = env("SABA_USERNAME");
        $this->password = env("SABA_PASSWORD");
        $this->line = env("SABA_LINE");
        $this->address = env("SABA_ADDRESS");
    }

    public function send(array $numbers, array $messages) {
        if (count($numbers) === 0) {
            throw new HttpException(400, 'You need to pass atleast one phone number!');
        }
        if (count($messages) === 0) {
            throw new HttpException(400, 'You need to pass atleast one message!');
        }
    
        if (trim(end($messages)) !== 'لغو۱۱') {
            $messages[] = 'لغو۱۱';
        }

        $parameters = [
            'username' => $this->username,
            'password' => $this->password,
            'line' => $this->line,
            'life_time' => '60',
            'mobile' => implode(',', $numbers),
            'messages' => implode('\n', $messages),
        ];

        return Http::get($this->address, $parameters);
    }
}
