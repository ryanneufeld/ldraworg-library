<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Models\Part;
use App\Models\PartRelease;
use App\LDraw\FileUtils;

class RelatedPartsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $parts = Part::lazy();
      foreach ($parts as $part) {
        if ($part->type->format == 'png') continue;
        $part->updateSubparts();
        $part->updateImage();
      }
      $parts = Part::whereRelation('release', 'short', 'unof')->lazy();
      foreach ($parts as $part) {
        $part->updateUncertifiedSubpartsCache();
        $opart = Part::findByName($part->filename)->id;
        if (!empty($opart)) {
          $part->official_part->associate($opart);
          $opart->unofficial_part->associate($part);
        }  
      }
    }
}
