<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakeReleaseZip implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $ids;

    public $uniqueFor = 3600;
    public $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $ids)
    {
      $this->$ids = $ids;
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

      $uzip = new \ZipArchive;
      $uzip->open(storage_path('app/library/updates/staging/lcad'. $release['short'] . '.zip'), \ZipArchive::CREATE);
  
      Storage::disk('library')->copy('updates/completeBase.zip','updates/staging/complete.zip');
      $zip = new \ZipArchive;
      $zip->open(storage_path('app/library/updates/staging/complete.zip'));
  
      // Create update zip and update complete zip
      foreach(Part::whereIn('id', $ids)->get() as $part) {      
        if($part->isTexmap()) {
          $content = base64_decode($part->body->body);
        }
        else {
          $content = rtrim($part->header);
  
          // Replae type with release type line
          $utype = '0 !LDRAW_ORG Unofficial_' . $part->type->type;
          $rtype = '0 !LDRAW_ORG ' . $part->type->type . ' UPDATE ' . $release['name'];
          $content = str_replace($utype, $rtype, $content);
  
          // Add release history line
          if (stripos($content, '!HISTORY') === false) $content .= "\n";
          $content .= "\n0 !HISTORY " . date_format(date_create(), "Y-m-d") . " [" . Auth::user()->name . "] Official Update " . $release['name'];
  
          //Dos line endings
          $content = FileUtils::unix2dos($content . "\n\n" . $part->body->body);
        }
        $uzip->addFromString('ldraw/' . $part->filename, $content);
        $zip->addFromString('ldraw/' . $part->filename, $content);
      }
}
