<?php

namespace App\LDraw;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use Spatie\TemporaryDirectory\TemporaryDirectory;

use App\Models\Part;

class ZipFiles {
    public static function unofficialZip(Part $part, ?string $oldfilename = null): void
    {
        $tempDir = TemporaryDirectory::make()->deleteWhenDestroyed();
        $zip = new \ZipArchive();
        if (Storage::disk('library')->exists('unofficial/ldrawunf.zip')) {
            $zip->open(Storage::disk('library')->path('unofficial/ldrawunf.zip'));
            if (!is_null($oldfilename)) {
                $zip->deleteName($oldfilename);
            } 
            $path = $tempDir->path($part->filename);
            file_put_contents($path, $part->get());
            $time = $part->lastChange()->format('U');
            touch($path, $time);
            $zip->addFile($path, $part->filename);
            $zip->setMtimeName($part->filename, $time);
            $zip->setCompressionName($part->filename, \ZipArchive::CM_DEFAULT);
            $zip->close();        
        }
        else {
            $zip->open(Storage::disk('library')->path('unofficial/ldrawunf.zip'), \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            Part::unofficial()->each(function (Part $part) use ($zip, $tempDir) {
                $path = $tempDir->path($part->filename);
                file_put_contents($path, $part->get());
                $time = $part->lastChange()->format('U');
                touch($path, $time);
                $zip->addFile($path, $part->filename);
                $zip->setMtimeName($part->filename, $time);
                $zip->setCompressionName($part->filename, \ZipArchive::CM_DEFAULT);
            });        
            $zip->close();
        }
    }

    public static function completeZip(): void
    {
        $sdisk = config('ldraw.staging_dir.disk');
        $spath = config('ldraw.staging_dir.path');
        if (!Storage::disk($sdisk)->exists($spath)) {
            Storage::disk($sdisk)->makeDirectory($spath);
        }
        $sfullpath = realpath(config("filesystems.disks.{$sdisk}.root") . "/{$spath}");
        $zipname = "{$sfullpath}/complete.zip";
        $zip = new \ZipArchive();
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
  
}