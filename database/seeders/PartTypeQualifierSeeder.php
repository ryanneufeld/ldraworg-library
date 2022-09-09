<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\PartTypeQualifier;

class PartTypeQualifierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      PartTypeQualifier::create(['type' => 'Alias', 'name' => 'Shortcut']);
      PartTypeQualifier::create(['type' => 'Physical_Colour', 'name' => 'Physical Colour']);
      PartTypeQualifier::create(['type' => 'Flexible_Section', 'name' => 'Flexible Section']);
    }
}
