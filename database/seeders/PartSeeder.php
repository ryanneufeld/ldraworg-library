<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

use App\Models\Part;
use App\Models\PartType;
use App\Models\PartRelease;
use App\Models\PartLicense;
use App\LDraw\FileUtils;
use App\Models\User;

class PartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      foreach (['official','unofficial'] as $lib) {
        foreach (Storage::disk('local')->allDirectories('library/' . $lib) as $dir) {
          if (strpos($dir,'images') !== false || strpos($dir,'models') !== false) continue;
          $files = Storage::disk('local')->files($dir);
          foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == 'dat') {
              $text = FileUtils::cleanFileText(Storage::disk('local')->get($file));
              Part::updateOrCreateFromText($text);
            }

            elseif(pathinfo($file, PATHINFO_EXTENSION) == 'png') {
              $filename = substr($file, strpos($file, 'official') + 9);
              if (Part::findByName($filename, $lib == 'unofficial')) continue;
              $pt = PartType::firstWhere('folder', pathinfo($filename,PATHINFO_DIRNAME) . '/');
              $relcomp = $lib == "official" ?  : "=";
              Part::create([
                'user_id' => User::findByName('unknown')->id,
                'part_release_id' => $lib == "official" ? PartRelease::current()->id : PartRelease::unofficial()->id,
                'part_license_id' => PartLicense::defaultLicense()->id,
                'filename' => $filename,
                'description' => $pt->name . ' ' . basename($file),
                'part_type_id' => $pt->id,
              ]);
            }
          }  
        }  
      }
    }    
}
