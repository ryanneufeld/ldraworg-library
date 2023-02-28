<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Part;

class UpdateSubparts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $part;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Part $part)
    {
      $this->part = $part;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $this->part->updateSubparts();
    }
}
