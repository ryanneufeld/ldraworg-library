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
      foreach(Part::lazy() as $p) {
        if ($p->user->license->id != $p->license->id) {
          $md = $p->minor_edit_data;
          $old_lid = $p->part_license_id;
          $p->part_license_id = $p->user->license->id;
          $md['license'] = \App\Models\PartLicense::find($old_lid)->name . " to " . $p->license->name;
          $p->minor_edit_data = $md;
          $p->save();
          $p->refresh();
          $p->refreshHeader();
        }
      }
      
    }
}
