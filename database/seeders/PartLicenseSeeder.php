<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\LDraw\MetaData;
use App\Models\PartLicense;

class PartLicenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $lics = config('ldraw.part_licenses');
      foreach ($lics as $name => $text) {
        PartLicense::create(['name' => $name, 'text' => $text]);
      }
    }
}
