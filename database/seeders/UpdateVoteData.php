<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\Part;

class UpdateVoteData extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      // Update the cached vote count
      foreach (Part::unofficial()->lazy() as $part) {
        $part->updateVoteData(true);
      }
    }
}    