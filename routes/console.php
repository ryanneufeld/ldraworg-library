<?php

use Illuminate\Support\Facades\Schedule;
use App\LDraw\ScheduledTasks\SendDailyDigest;
use App\LDraw\ScheduledTasks\UpdateTrackerHistory;
use App\Jobs\DailyMaintenance;

Schedule::command('queue:prune-batches')->daily();
Schedule::call(new SendDailyDigest(new \DateTime('yesterday')))->dailyAt('01:30')->environments(['production']);    
Schedule::call(new UpdateTrackerHistory())->daily();
Schedule::job(new DailyMaintenance())->daily();
