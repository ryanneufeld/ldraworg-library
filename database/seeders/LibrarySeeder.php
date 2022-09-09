<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Models\Part;
use App\Models\PartType;
use App\Models\PartRelease;
use App\Models\File;

class LibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $unof = PartRelease::firstWhere('short', 'unof')->id;
      $of = PartRelease::firstWhere('short', 'original')->id;
      $tex = PartType::firstWhere('type', 'Texmap')->id;
      // Preload parts and files into the database
      foreach (['official','unofficial'] as $lib) {
        foreach (Storage::disk('public')->allDirectories($lib) as $dir) {
          if (strpos($dir,'images') !== false || strpos($dir,'models') !== false) continue;
          $files = Storage::disk('public')->files($dir);
          foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == 'dat' || pathinfo($file, PATHINFO_EXTENSION) == 'png') {
              $filename = mb_substr($file, mb_strpos($file, 'official') + 9);
              $lib == 'unofficial' ? $off_part = Part::firstWhere('filename', $filename) : $off_part = null;
              $part = Part::create([
                  'user_id' => 1,
                  'part_category_id' => null,
                  'part_release_id' => $lib == 'unofficial' ? $unof : $of,
                  'filename' => $filename,
                  'description' => '',
                  'part_type_id' => pathinfo($file, PATHINFO_EXTENSION) == 'png' ? $tex : 1,
                  'part_type_qualifier_id' => null,
                  'unofficial' => $lib == 'unofficial',
                  'official_part_id' => isset($off_part) ? $off_part->id : null,
                ]);
              $pfile = File::create([
                'disk' => 'public',
                'path' => $file,
                'part_id' => $part->id,
              ]);
            }
          }
        }
      }
    }
}
