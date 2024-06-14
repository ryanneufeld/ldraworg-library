<?php

namespace App\Console\Commands;

use App\Models\Part;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

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
        Part::unofficial()->whereHas('descendantsAndSelf', fn (Builder $q) => $q->where('vote_sort', '>', 2))->update(['ready_for_admin' => false]);
    }
}
