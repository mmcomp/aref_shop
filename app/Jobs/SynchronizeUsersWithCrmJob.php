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
        try {
            $response = Http::post(env('CRM_ADD_STUDENT_URL'), [
                "students" => [
                    0 => [
                        "phone" => $this->user->email,
                    ],
                ],
            ]);
            if ($response->getStatusCode() == 200) {
                UserSync::create([
                    "users_id" => $this->user->id,
                    "status" => "successful",
                    "error_message" => null,
                ]);
            }
        } catch (Exception $e) {
            Log::info("CRM ran into a problem in synchronize users!" . json_encode($e->getMessage()));
            UserSync::create([
                "users_id" => $this->user->id,
                "status" => "failed",
                "error_message" => json_encode($e->getMessage()),
            ]);
        }
    }
}
