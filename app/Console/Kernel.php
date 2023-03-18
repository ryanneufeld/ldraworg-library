<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\LDraw\ScheduledTasks\SendDailyDigest;
use App\LDraw\ScheduledTasks\UpdateTrackerHistory;
use App\Jobs\UpdateUncertifiedSubparts;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
      $schedule->command('telescope:prune')->daily();
      $schedule->command('queue:prune-batches')->daily();
      $schedule->call(new SendDailyDigest(new \DateTime('yesterday')))->dailyAt('01:30');
      $schedule->job(new UpdateUncertifiedSubparts)->dailyAt('01:00');
      $schedule->call(new UpdateTrackerHistory)->daily();
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
