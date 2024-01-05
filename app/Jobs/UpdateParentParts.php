<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Part;
use App\LDraw\PartManager;

class UpdateParentParts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        protected Part $part
    )
    {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!is_null($this->part->official_part_id)) {
            foreach (Part::find($this->part->official_part_id)->parents()->official()->get() as $p) {
                app(PartManager::class)->loadSubpartsFromBody($p);
            }
        }
        foreach ($this->part->ancestors as $p) {
            app(PartManager::class)->updatePartImage($p);
        }
    }
}
