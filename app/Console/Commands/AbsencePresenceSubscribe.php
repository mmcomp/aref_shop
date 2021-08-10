<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Models\UserVideoSession;
use App\Models\ProductDetailVideo;

class AbsencePresenceSubscribe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'absence:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'subscribe to absence-presence channel';

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
        Redis::subscribe(['absence-presence-channel'], function ($message) {
            Log::info('absence-presence-channel '. $message);
            $json_decode_message = json_decode($message, true);
            $product_detail_videos_id = $json_decode_message["product_detail_viedos_id"];
            $product_detail_video = ProductDetailVideo::where('is_deleted', false)->find($product_detail_videos_id);
            $users_id = $json_decode_message["users_id"];
            $type = $json_decode_message["type"];
            $isFirst = isset($json_decode_message["isFirst"]) && $json_decode_message["isFirst"];
            $user_video_session = UserVideoSession::where('video_sessions_id', $product_detail_video->video_sessions_id)->where('users_id', $users_id)->first();
            // Log::info($user_video_session->id);
            if ($type == "online") {
                if ($user_video_session->online_started_at == null && $isFirst) {
                    $user_video_session->online_started_at = now();
                    if (!$isFirst) {
                        $user_video_session->online_spend += 5;
                    }
                } else if ($user_video_session->online_started_at != null) {
                    $user_video_session->online_exited_at = now();
                    $user_video_session->online_spend += 5;
                }
            } else {
                if ($user_video_session->offline_started_at == null && $isFirst) {
                    $user_video_session->offline_started_at = now();
                    if (!$isFirst) {
                        $user_video_session->offline_spend += 5;
                    }
                } else if($user_video_session->offline_started_at != null) {
                    $user_video_session->offline_exited_at = now();
                    $user_video_session->offline_spend += 5;
                }
            }
            $user_video_session->save();

        });
    }
}
