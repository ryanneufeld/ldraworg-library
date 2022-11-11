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
      PartType::create(['type' => 'Part', 'name' => 'Part', 'folder' => 'parts', 'format' => 'dat']);
      PartType::create(['type' => 'Subpart', 'name' => 'Subpart', 'folder' => 'parts/s', 'format' => 'dat']);
      PartType::create(['type' => 'Primitive', 'name' => 'Primitive', 'folder' => 'p', 'format' => 'dat']);
      PartType::create(['type' => '8_Primitive', 'name' => '8 Segment Primitive', 'folder' => 'p/48', 'format' => 'dat']);
      PartType::create(['type' => '48_Primitive', 'name' => '48 Segment Primitive', 'folder' => 'p/8', 'format' => 'dat']);
      PartType::create(['type' => 'Shortcut', 'name' => 'Shortcut', 'folder' => 'parts', 'format' => 'dat']);
      PartType::create(['type' => 'Texmap', 'name' => 'TEXMAP Image', 'folder' => 'parts/textures', 'format' => 'png']);
      PartType::create(['type' => 'Subpart_Texmap', 'name' => 'Subpart TEXMAP Image', 'folder' => 'parts/textures/s', 'format' => 'png']);
      PartType::create(['type' => 'Primitive_Texmap', 'name' => 'Primitve TEXMAP Image', 'folder' => 'p/textures', 'format' => 'png']);
      PartType::create(['type' => 'Helper', 'name' => 'Helper', 'folder' => 'parts/h', 'format' => 'dat']);
    }
}
