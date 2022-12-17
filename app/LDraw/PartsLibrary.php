<?php

namespace App\LDraw;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

use App\Models\Part;
use App\LDraw\FileUtils;
use App\LDraw\PartCheck;

class PartsLibrary
{
  public static function scanForTypeMismatch() {
    $errors = [];
    foreach (['official','unofficial'] as $lib) {
      foreach (Storage::disk('local')->allDirectories('library/' . $lib) as $dir) {
        if (strpos($dir,'images') !== false || strpos($dir,'models') !== false) continue;
        $files = Storage::disk('local')->files($dir);
        foreach ($files as $file) {
          if (pathinfo($file, PATHINFO_EXTENSION) == 'dat') {
            $text = FileUtils::fixEncoding(Storage::disk('local')->get($file));
            $name = str_replace('\\','/', FileUtils::getName($text));
            if (strpos($file, "library/$lib/p/") !== false) {
              $offset = $lib == 'unofficial' ? 21 : 19;
              $fname = substr($file, strpos($file, "library/$lib/p/") + $offset);
            }
            else {
              $offset = $lib == 'unofficial' ? 25 : 23;
              $fname = substr($file, strpos($file, "library/$lib/p/") + $offset);
            }
            
            if ($fname !== $name) {
              $errors[] = $file;
            }
          }
        }
      }
    }
    return $errors;
  }
}
