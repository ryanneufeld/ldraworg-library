<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Part;
use App\LDraw\ZipFiles;

class UpdateZip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $part;
    protected $oldfilename;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Part $part, string $oldfilename = null)
    {
        $this->part = $part;
        $this->oldfilename = $oldfilename;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ZipFiles::unofficialZip($this->part, $this->oldfilename);
    }
}
