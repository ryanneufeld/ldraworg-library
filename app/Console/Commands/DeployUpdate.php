<?php

namespace App\Console\Commands;

use App\Events\PartHeaderEdited;
use App\Events\PartReviewed;
use App\Events\PartSubmitted;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\User;
use Illuminate\Console\Command;

class DeployUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lib:update';

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
        Part::unofficial()->where('can_release', true)->where('vote_sort', 1)->update([
            'marked_for_release' => true
        ]);
    }
}
