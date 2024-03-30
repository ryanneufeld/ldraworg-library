<?php

namespace App\Console\Commands;

use App\Mail\DailyDigest;
use App\Mail\TestEmail;
use App\Models\PartEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lib:test-email {user} {--daily}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh app cache after code update';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $rn = $this->argument('user');
        $user = \App\Models\User::firstWhere('name', $rn);
        if ($this->option('daily')) {
            $date = new \DateTime('yesterday');
            $events = PartEvent::whereBetween('created_at', [$date, now()])
                    ->whereIn('part_id', $user->notification_parts->pluck('id'))->oldest()->get();
            Mail::to($user)->send(new DailyDigest($date, $events));
        } else {
            Mail::to($user)->send(new TestEmail(now(), 'This is a test message from the Parts Tracker'));
        }
    }
}
