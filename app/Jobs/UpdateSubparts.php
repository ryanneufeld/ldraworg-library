<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Part;

class UpdateSubparts implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $updateuncert;

    public $uniqueFor = 3600;
    public $timeout = 3600;

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
      $u = $this->updateuncert;
      Part::chunk(500, function ($parts) use ($u) {
        foreach($parts as $part) {
          $part->updateSubparts($u);
        }
      });
    }
}
