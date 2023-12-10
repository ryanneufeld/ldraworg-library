<?php

namespace App\Console\Commands;

use App\Jobs\UpdateZip;
use App\Models\Part;
use Illuminate\Console\Command;

class RefreshZip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pt:refresh-zip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the unofficial zip file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        UpdateZip::dispatch(Part::unofficial()->first());
    }
}
