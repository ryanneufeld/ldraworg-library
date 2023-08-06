<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class RefreshDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:db-refresh';

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
        if (app()->environment('local') && file_exists(env('LIBRARY_SQL_FILE'))) {
            $sql = env('LIBRARY_SQL_FILE');
              
            $db = [
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'host' => env('DB_HOST'),
                'database' => env('DB_DATABASE')
            ];
            
            Process::run("mysql --user={$db['username']} --password={$db['password']} --host={$db['host']} --database {$db['database']} < $sql");
            $this->call('migrate');
            $this->call('app:update');
        } else {
            $this->info('This command cannot be run the the production environment');
        }
     }
}
