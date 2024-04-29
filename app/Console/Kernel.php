<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
       Commands\CheckAllWeeklyPrograms::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $now = now()->format('Y-m-d H:i:s');
            $users = User::where("blocked", ">", $now)->pluck("id");
            $blockedUsers["blocked_users"] = $users->toArray();
            if ($this->putBlockedUserToRedis($blockedUsers["blocked_users"])) {
                return $blockedUsers;
            }
            return "";
        })->everyFifteenMinutes();

        $schedule->command('app:check-weekly-programs')
            ->fridays()
            ->at('23:00');
    }

    public function putBlockedUserToRedis($blocketUsers)
    {
        Redis::del('blocked_users');
        $redis = Redis::connection();
        $redis->set('blocked_users', json_encode($blocketUsers));
        return true;
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
