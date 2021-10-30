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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("CRM_ADD_STUDENT");
        try {
            Log::info("CRM_ADD_STUDENT_Try" . json_encode($this->user));
            // $response = Http::post(env('CRM_ADD_STUDENT_URL'), [
            //     "students" => [
            //         0 => [
            //             "phone" => $this->user->email,
            //             "last_name" => $this->user->last_name,
            //             'introducing' => 'عضویت در سایت'
            //         ],
            //     ],
            // ]);

            // $response = Http::withoutVerifying()
            //     ->withOptions(["verify" => false])->post("http://crm.aref-group.ir/api/students", [
            //         "students" => [
            //             0 => [
            //                 "phone" => $this->user->email,
            //                 "last_name" => $this->user->last_name,
            //                 'introducing' => 'عضویت در سایت'
            //             ],
            //         ],
            //     ]);

            // Log::info("CRM_ADD_STUDENT_SUCCESS");
            // if ($response->getStatusCode() == 200) {
            //     Log::info("CRM_ADD_STUDENT_SUCCESS_200");
            //     UserSync::create([
            //         "users_id" => $this->user->id,
            //         "status" => "successful",
            //         "error_message" => null,
            //     ]);
            // } else {
            //     Log::info("CRM_ADD_STUDENT_SUCCESS:" . $response->getStatusCode());
            //     UserSync::create([
            //         "users_id" => $this->user->id,
            //         "status" => "failed",
            //         "error_message" => "the response was not successful" . $response->getStatusCode(),
            //     ]);
            // }
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
