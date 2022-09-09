<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\PartType;

class PartTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      PartType::create(['type' => 'Part', 'name' => 'Part']);
      PartType::create(['type' => 'Subpart', 'name' => 'Subpart']);
      PartType::create(['type' => 'Primitive', 'name' => 'Primitive']);
      PartType::create(['type' => '8_Primitive', 'name' => '8 Segment Primitive']);
      PartType::create(['type' => '48_Primitive', 'name' => '48 Segment Primitive']);
      PartType::create(['type' => 'Shortcut', 'name' => 'Shortcut']);
      PartType::create(['type' => 'Texmap', 'name' => 'TEXMAP Image']);
    }
}
