<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

use function GuzzleHttp\json_decode;

class SynchronizeUsersWithCrmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $request;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request->all();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
 
        $tmp = [
            "students" => [
                ["phone" => "09153139388"],
                ["phone" => "09153255597"]
            ]
        ];
        $res = json_encode($tmp);
        $response = Http::post('http://localhost:8001/api/students', 
          //['json' => $tmp]
          ['json' => $this->request] 
        );
        //echo $response->getStatusCode();
    }
}
