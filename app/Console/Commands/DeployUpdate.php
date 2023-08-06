<?php

namespace App\Console\Commands;

use App\Models\PartEvent;
use App\Models\PartType;
use Illuminate\Console\Command;
use App\Models\User;

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
        /*
        User::each(function (User $u) {
            if ($u->hasRole('Legacy User')) {
                $u->account_type = 1;
                $u->save();
            } elseif ($u->hasRole('Synthetic User')) {
                $u->account_type = 2;
                $u->save();
            } else {
                $u->account_type = 0;
                $u->save();
            }               
        });
        
        $ptadmin = User::ptadmin();
        $ptadmin->account_type = 2;
        $ptadmin->save();

        PartEvent::whereRelation('part_event_type', 'slug', 'rename')->each(function (PartEvent $e) {
            if (preg_match('#^part (.*) was renamed to (.*)$#', $e->comment, $matches)) {
                $e->moved_to_filename = str_replace('\'','', $matches[2]);
                $e->moved_from_filename = str_replace('\'','', $matches[1]);
                $e->save();
            }
        });

        $pt = PartType::firstWhere('type', 'Texmap');
        $pt->type = 'Part_Texmap';
        $pt->name = 'Part TEXMAP Image';
        $pt->save();
        */
    }  

}
