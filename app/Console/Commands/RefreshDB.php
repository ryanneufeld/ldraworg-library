<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class RefreshDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lib:refresh-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the local DB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (app()->environment('local') && Storage::disk('local')->exists('db/lib.sql')) {
            $this->info('Copying production db backup');
            $this->call('migrate:fresh');
            DB::table('migrations')->truncate();
            $db = config('database.connections.mysql.database');
            $db_user = config('database.connections.mysql.username');
            $db_pw = config('database.connections.mysql.password');
            $backup = Storage::disk('local')->path('db/lib.sql');
            $result = Process::run("mysql --user={$db_user} --password={$db_pw} --database={$db} < {$backup}");
            $this->info($result->output());
            $this->call('migrate');
            $this->info('Running update');
            $this->call('lib:update');
        } else {
            $this->info('This command cannot be run the the production environment');
        }
    }
}
