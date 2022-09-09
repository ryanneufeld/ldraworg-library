<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Models\Part;

class RelatedPartsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $parts = Part::all()->lazy();
      foreach ($parts as $part) {
        $file = $part->file;
        $part->subparts()->sync([]);
        $subparts = [];
        $textures = [];
        $subpart_match_pattern = '#\n\s?(0\s+!:\s+)?1\s+([-\.\d]+\s+){13}(?P<subpart>.*?\.dat)#ius';
        if (preg_match_all($subpart_match_pattern, $file, $matches) > 0) {
          $subparts = array_unique($matches['subpart']);
        }

        $texture_match_pattern = '#\n\s?0\s+!TEXMAP\s+(START|NEXT)\s+(PLANAR|CYLINDRICAL|SPHERICAL)\s+([-\.\d]+\s+){9,11}(?P<texture1>.*?\.png)(\s+GLOSSMAP\s+(?P<texture2>.*?\.png)])?#ius';
        if (preg_match_all($texture_match_pattern, $file, $matches) > 0) { 
          $textures = $matches['texture1'];
          if (isset($matches['texture2'])) $textures = array_merge($textures, $matches['texture2']);
          $textures = array_unique($textures);
        }
        
        foreach ($subparts as $subpart) {
          $subpart = str_replace('\\', '/', $subpart);
          $subp = Part::where(function($query) use ($subpart) {
              $query->where('filename', 'p/' . $subpart)
              ->orWhere('filename', 'parts/' . $subpart);
          })
          ->where('unofficial', $part->unofficial)->first();
          $part->subparts()->attach($subp);
          unset($subp);
        }  
        foreach ($textures as $texture) {
          $texture = str_replace('\\', '/', $texture);
          $subp = Part::where(function($query) use ($texture) {
              $query->where('filename', 'parts/textures/' . $texture)
              ->orWhere('filename', 'p/textures/' . $texture);
          })
          ->where('unofficial', $part->unofficial)->first();
          $part->subparts()->attach($subp);
          unset($subp);
        }  
      }
      $parts  = Part::where('unofficial', true)->lazy();
      foreach ($parts as $part) {
        $part->updateUncertifiedSubpartsCache();
      }
    }
}
