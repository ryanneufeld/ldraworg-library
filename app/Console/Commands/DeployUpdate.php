<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use App\Models\Part;

class DeployUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the app after update deployments';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
      
      foreach (Part::where('description', 'Missing')->get() as $p) {
        //$p->deleteRelationships();
        $p->parents()->sync([]);
        $p->deleteQuietly();
      }
      
      \App\Jobs\UpdateSubparts::dispatch();
    }
}
