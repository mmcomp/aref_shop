<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Models\ChatMessage;
use App\Models\UserVideoSession;
use App\Models\ProductDetailVideo;

class RedisSubscribe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to a Redis channel';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Redis::subscribe([env('REDIS_CHAT_CHANEL', 'chat-channel')], function ($message) {
            Log::info("message recieved " . $message);
            $chatData = json_decode($message);
            $userId = Redis::get('token_' . $chatData->Token);

            ChatMessage::create([
                'users_id' => $userId,
                'ip_address' => "",
                'video_sessions_id' => $chatData->Data->video_session_id,
                'message' => $chatData->Data->msg
            ]);
        });
    }
}
