<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\Part;

class UpdateSubpartsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      foreach (Part::where('unofficial', true)->lazy() as $part) {
        $part->updateUncertifiedSubpartsCache();
      }
    }
}    