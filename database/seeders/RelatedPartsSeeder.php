<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
      foreach (Part::lazy() as $part) {
        $part->updateSubparts();
      }
    }
}
