<?php

namespace App\Console\Commands;

use App\Mail\TestEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lib:test-email {user}';

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
        Mail::to($user)->send(new TestEmail(now(), 'This is a test message from the Parts Tracker'));
    }
}
