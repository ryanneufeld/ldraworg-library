<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

use App\Models\Part;

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
      $zip = new \ZipArchive;
      if (Storage::disk('library')->exists('unofficial/ldrawunf.zip')) {
        $zip->open(Storage::disk('library')->path('unofficial/ldrawunf.zip'));
        if (!is_null($this->oldfilename)) $zip->deleteName($this->oldfilename);
        $zip->addFromString($this->part->filename, $this->part->get());
        $zip->close();        
      }
      else {
        $zip->open(Storage::disk('library')->path('unofficial/ldrawunf.zip'), \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        Part::unofficial()->each(function (Part $part) use ($zip) {
          $zip->addFromString($part->filename, $part->get());
        });        
        $zip->close();
      }
    }
}
