<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
        $r = \App\Models\PartRelease::where('short', 'unof')->first();

        Part::where('part_release_id', $r->id)->update(['part_release_id' => null]);
        \App\Models\PartEvent::where('part_release_id', $r->id)->update(['part_release_id' => null]);
        $r->delete();
    }
      

}
