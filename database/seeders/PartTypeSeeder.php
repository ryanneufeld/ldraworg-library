<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\PartType;
use App\LDraw\MetaData;

class PartTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $types = MetaData::getPartTypes();
      foreach ($types as $type => $data) {
        $data['type'] = $type;
        PartType::create($data);
      }
    }
}
