<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\PartCategory;
use App\LDraw\MetaData;

class PartCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $categories = MetaData::getCategories();
      foreach ($categories as $category) {
        PartCategory::create(['category' => $category]);
      }
    }
}
