<?php
namespace App\LDraw;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

use App\Models\Part;
use App\Models\PartRelease;

class ZipFiles {
  public static function unofficialZip(Part $part, string $oldfilename = null) {
    $zip = new \ZipArchive;
    if (Storage::disk('library')->exists('unofficial/ldrawunf.zip')) {
      $zip->open(Storage::disk('library')->path('unofficial/ldrawunf.zip'));
      if (!is_null($oldfilename)) $zip->deleteName($oldfilename);
      $zip->addFromString($part->filename, $part->get());
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

  public static function completeZip() {
    $sdisk = config('ldraw.staging_dir.disk');
    $spath = config('ldraw.staging_dir.path');
    if (!Storage::disk($sdisk)->exists($spath))
      Storage::disk($sdisk)->makeDirectory($spath);
    $sfullpath = realpath(config("filesystems.disks.$sdisk.root") . "/$spath");
    $zipname = "$sfullpath/complete.zip";
    $zip = new \ZipArchive;
    $zip->open($zipname, \ZipArchive::CREATE);
    foreach (Storage::disk('library')->allFiles('official') as $filename) {
      $zipfilename = str_replace('official/', '', $filename);
      $content = Storage::disk('library')->get($filename);
      $zip->addFromString('ldraw/' . $zipfilename, $content);
    }
    $zip->close();
    Part::official()->chunk(500, function (Collection $parts) use ($zip, $zipname) {
      $zip->open($zipname);
      foreach($parts as $part) {
        $content = $part->get();
        $zip->addFromString('ldraw/' . $part->filename, $content);
      }
      $zip->close();
    });
  }
  
  public static function releaseZip() {
    $release = PartRelease::current();
    $sdisk = config('ldraw.staging_dir.disk');
    $spath = config('ldraw.staging_dir.path');
    if (!Storage::disk($sdisk)->exists($spath))
      Storage::disk($sdisk)->makeDirectory($spath);
    $sfullpath = realpath(config("filesystems.disks.$sdisk.root") . "/$spath");
    $uzipname = "$sfullpath/lcad{$release->short}.zip";
    $zipname = "$sfullpath/complete.zip";
    $uzip = new \ZipArchive;
    $uzip->open($uzipname, \ZipArchive::CREATE);

    $zip = new \ZipArchive;
    $zip->open($zipname, \ZipArchive::CREATE);

    foreach (Storage::disk('library')->allFiles('official') as $filename) {
      $zipfilename = str_replace('official/', '', $filename);
      $content = Storage::disk('library')->get($filename);
      $zip->addFromString('ldraw/' . $zipfilename, $content);
    }
    $zip->close();

    $zip->open($zipname);
    foreach (Storage::disk($sdisk)->allFiles("$spath/ldraw") as $filename) {
      $zipfilename = str_replace("$spath/", '', $filename);
      $content = Storage::disk($sdisk)->get($filename);
      $uzip->addFromString($zipfilename, $content);
      if ($zip->getFromName($zipfilename) !== false)
        $zip->deleteName($zipfilename);
      $zip->addFromString($zipfilename, $content);
    }
    $zip->close();

    // This has to be chunked because php doesn't write the file to disk immediately
    // Trying to hold the entire library in memory will cause an OOM error
    Part::official()->chunk(500, function (Collection $parts) use ($zip, $zipname, $uzip, $release) {
      $zip->open($zipname);
      foreach($parts as $part) {
        $content = $part->get();
        $zip->addFromString('ldraw/' . $part->filename, $content);
        if ($part->part_release_id == $release->id) 
          $uzip->addFromString('ldraw/' . $part->filename, $content);
      }
      $zip->close();
    });
    $uzip->close();
    Storage::disk('library')->copy('updates/complete.zip', "updates/complete-{$release->short}.zip");
    Storage::disk('library')->writeStream("updates/lcad{$release->short}.zip", Storage::disk($sdisk)->readStream("$spath/lcad{$release->short}.zip"));
    Storage::disk('library')->writeStream("updates/complete.zip", Storage::disk($sdisk)->readStream("$spath/complete.zip"));
  }
}