<?php

use App\LDraw\ScheduledTasks\SendDailyDigest;
use App\LDraw\ScheduledTasks\UpdateTrackerHistory;
use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:prune-batches')->daily();
Schedule::call(new SendDailyDigest)->dailyAt('01:30')->environments(['production']);
Schedule::call(new UpdateTrackerHistory)->daily();
Schedule::command('lib:daily-maintenance')->daily();
