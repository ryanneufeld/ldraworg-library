<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Part;

class UpdateSubparts implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected bool $updateuncert;

    public $timeout = 900;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(bool $updateuncert = false)
    {
      $this->updateuncert = $updateuncert;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

      if (!empty($this->batch()->id) && $this->batch()->cancelled()) {
         return;
      }
      foreach(Part::lazy() as $p) {
        $p->updateSubparts($this->updateuncert);
      }
    }
}
