<?php

namespace App\Console;

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
         // 1. Every day at 3:00 PM, check for students who never showed up
         $schedule->call('App\Http\Controllers\AttendanceController@notifyDailyAbsentees')
                  ->dailyAt('15:00');
     
         // 2. Every Wednesday and Friday at 4:00 PM, check for habitual lateness
         // (This covers your "3 lates per week" rule)
         $schedule->call('App\Http\Controllers\AttendanceController@notifyChronicLatecomers')
                  ->days([Schedule::WEDNESDAY, Schedule::FRIDAY])
                  ->at('16:00');
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
