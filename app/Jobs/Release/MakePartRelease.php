<?php

namespace App\Jobs\Release;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Collection;

use App\Models\User;

class MakePartRelease implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Collection $parts;
    protected User $user;

    public $uniqueFor = 1800;
    public $timeout = 1800;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Collection $parts, User $user)
    {
      $this->parts = $parts;
      $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      \App\Models\PartRelease::createRelease($this->parts, $this->user);
    }
}
