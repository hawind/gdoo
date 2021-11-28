<?php

namespace App\Console;

use DB;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // */1 * * * * /www/web/php70/bin/php /www/htdocs/shenghua.app/artisan schedule:run --env=production 1>> /dev/null 2>&1
        
        $rows = DB::table('cron')->where('status', 1)->get();
        if ($rows) {
            foreach ($rows as $row) {
                if ($row['expression'] && $row['command']) {
                    $schedule->command($row['command'])->cron($row['expression']);
                }
            }
        }
        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
