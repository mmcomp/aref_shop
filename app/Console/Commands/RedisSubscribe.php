<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Models\ChatMessage;
use App\Models\UserVideoSession;
use App\Models\ProductDetailVideo;
use Predis\Client;

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
        $client = new Client();
        Redis::subscribe([env('REDIS_CHAT_CHANEL', 'chat-channel')], function ($message) use ($client) {
            Log::info("message recieved " . $message);
            $chatData = json_decode($message);
            $keys = $client->keys(env('REDIS_PREFIX', 'aref_shop_') . 'user_*');
            Log::info("keys :" . var_export($keys, true));
            $tokens = [];
            foreach($keys as $key) {
                $token = $client->get($key);
                $tokens[$token] = str_replace(env('REDIS_PREFIX', 'aref_shop_') . 'user_', "", $key);
            }

            ChatMessage::create([
                'users_id' => $tokens[$chatData->Token],
                'ip_address' => "",
                'video_sessions_id' => $chatData->Data->video_session_id,
                'message' => $chatData->Data->msg
            ]);
        });
    }
}
