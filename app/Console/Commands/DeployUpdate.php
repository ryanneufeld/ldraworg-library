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
        $this->info('Nothing to update');
        /*
        $this->info('Updating subparts');
        Part::with('body')->each(function (Part $p){
            $s = app(\App\LDraw\Parse\Parser::class)->getSubparts($p->body->body);
            $p->setSubparts($s ?? []);
        });
        */
    }  

}
