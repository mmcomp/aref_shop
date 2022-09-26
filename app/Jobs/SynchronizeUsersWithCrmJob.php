<?php

namespace App\Jobs;

use App\Models\UserSync;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SynchronizeUsersWithCrmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute cUrl
     *
     * @return object
     */
    public function getCrmData($params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('CRM_ADD_STUDENT_URL'));
        $data_string = json_encode($params, JSON_UNESCAPED_UNICODE);
        //curl_setopt($ch, CURLOPT_URL,"http://192.168.1.81/test.php");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string)
        ]);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * Execute the job.
     *
     * @return object
     */
    public function handle()
    {
        Log::info("CRM_ADD_STUDENT");
        try {
            $response = $this->getCrmData( [
                "students" => [
                    0 => [
                        "phone" => $this->user->email,
                        "last_name" => $this->user->last_name,
                        'introducing' => 'عضویت در سایت'
                    ],
                ],
            ]);

            if ( count($response->added_ids)>0) {
                Log::info("CRM_ADD_STUDENT_SUCCESS_200");
                UserSync::create([
                    "users_id" => $this->user->id,
                    "status" => "successful",
                    "error_message" => null,
                ]);
            } 
        } catch (Throwable $e) {
            Log::info("CRM ran into a problem in synchronize users!" . json_encode($e->getMessage()));
            UserSync::create([
                "users_id" => $this->user->id,
                "status" => "failed",
                "error_message" => json_encode($e->getMessage()),
            ]);
        }
    }
}
