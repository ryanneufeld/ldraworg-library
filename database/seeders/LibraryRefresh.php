<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\Part;

class LibraryRefresh extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::disableQueryLog();
      
      Part::chunk(100, function ($parts) {
          foreach ($parts as $part) {
            $part->refreshAll();
            $part->save();
            unset($part);
            gc_collect_cycles();
          }
      });
      foreach (Part::where('unofficial', true)->lazy() as $part) {
        $part->updateUncertifiedSubpartsCache();
      }
    }
}
