<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

use App\Model\Part;

class UpdateZip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $filename;
    protected $newfilename;
    protected $contents;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filename, $contents, $newfilename = null)
    {
        $this->filename = $filename;
        $this->newfilename = $newfilename;
        $this->contents = $contents;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $this->contents = base64_decode($this->contents);
      $zip = new \ZipArchive;
      if (Storage::disk('library')->exists('unofficial/ldrawunf.zip')) {
        $zip->open(storage_path('app/library/unofficial/ldrawunf.zip'), \ZipArchive::CREATE);
        if (pathinfo($this->filename, PATHINFO_EXTENSION) == 'dat') $contents = preg_replace('#\R#u', "\r\n", $this->contents);
        if (is_null($this->newfilename)) {
          $zip->addFromString($this->filename, $this->contents);
        }
        else {
          $zip->deleteName($this->filename);
          $zip->addFromString($this->newfilename, $this->contents);
        }
        $zip->close();        
      }
      else {
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
        
      }
    }
}
