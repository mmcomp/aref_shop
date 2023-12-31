<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckWeeklyPrograms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-weekly-programs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        echo "SALAM\n";
    }
}
