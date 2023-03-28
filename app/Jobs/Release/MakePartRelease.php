<?php

namespace App\Jobs\Release;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\LDraw\PartUpdate;
use App\Models\User;

class MakePartRelease implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ids;
    protected User $user;

    public $uniqueFor = 3600;
    public $timeout = 3600;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Array $ids, User $user)
    {
      $this->ids = $ids;
      $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      PartUpdate::releaseParts($this->ids, $this->user);
    }
}
