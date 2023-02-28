<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

use App\Model\Part;

class CreateZip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $zip = new \ZipArchive;
      $zip->open(storage_path('app/library/unofficial/ldrawunf.zip'), \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
      $dirs = Storage::disk('library')->allDirectories('unofficial');
      foreach(Storage::disk('library')->allDirectories('unofficial') as $dir) {
        foreach(Storage::disk('library')->files($dir) as $file) {
          if (pathinfo($file, PATHINFO_EXTENSION) != 'dat' && pathinfo($file, PATHINFO_EXTENSION) != 'png') continue;
          $contents = Storage::disk('library')->get($file);
          if (pathinfo($file, PATHINFO_EXTENSION) == 'dat') $contents = preg_replace('#\R#u', "\r\n", $contents);
          $loc = str_replace('unofficial/', '', $file);
          $zip->addFromString($loc, $contents);
        }  
      }
      $zip->close();
    }
}