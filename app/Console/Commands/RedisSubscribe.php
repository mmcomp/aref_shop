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
    protected $signature = 'redis:subscribe';

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
        Redis::subscribe(['test-channel'], function ($message) use ($client) {
            Log::info("message recieved " . $message);
            $values = $client->hgetall(env('REDIS_PREFIX', 'aref_shop_') . 'user');
            $userId = 0;
            foreach ($values as $user_id => $token) {
                if ($token == json_decode($message)->Token) {
                    $userId = $user_id;
                }
            }
            ChatMessage::create([
                'users_id' => $userId,
                'ip_address' => "",
                'video_sessions_id' => json_decode($message)->Data->video_sessions_id,
                'message' => json_decode($message)->Data->msg
            ]);
        });

        // Redis::subscribe(['absence-presence-channel'], function ($message) {
        //     Log::info('absence-presence-channel '. $message);
        //     $json_decode_message = json_decode($message);
        //     $product_detail_videos_id = $json_decode_message->product_detail_videos_id;
        //     $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($product_detail_videos_id);
        //     $users_id = $json_decode_message->users_id;
        //     $type = $json_decode_message->type;
        //     $user_video_session = UserVideoSession::where('video_sessions_id', $product_detail_video->video_sessions_id)->where('users_id', $users_id)->first();
        //     Log::info($user_video_session->id);
        //     if ($type == "online") {
        //         if ($user_video_session->online_started_at == null) {
        //             $user_video_session->online_started_at = now();
        //         } else {
        //             $user_video_session->online_exited_at = now();
        //         }
        //         $user_video_session->online_spend += 5;
        //     } else {
        //         if ($user_video_session->offline_started_at == null) {
        //             $user_video_session->offline_started_at = now();
        //         } else {
        //             $user_video_session->offline_exited_at = now();
        //         }
        //         $user_video_session->offline_spend += 5;
        //     }
        //     $user_video_session->save();

        // });
    }
}
