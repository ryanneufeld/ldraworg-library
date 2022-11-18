<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\PartTypeQualifier;
use App\LDraw\MetaData;

class PartTypeQualifierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $types = MetaData::getPartTypeQualifiers();
      foreach ($types as $type => $name) {
        PartTypeQualifier::create(['type' => $type, 'name' => $name]);
      }
    }
}
