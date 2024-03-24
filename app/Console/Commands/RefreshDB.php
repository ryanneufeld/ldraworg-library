<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
        if (app()->environment('local') && file_exists(env('LIBRARY_SQL_FILE'))) {
            $sql = env('LIBRARY_SQL_FILE');

            $db = config('database.connections.sqlite.database');
            $backup = Storage::disk('local')->path('db/database.sqlite');
            copy($backup, $db);
            $this->call('lib:update');
        } else {
            $this->info('This command cannot be run the the production environment');
        }
    }
}
