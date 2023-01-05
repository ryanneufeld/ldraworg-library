<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\LDraw\LibraryImport;
use App\Models\Part;

class LibImport extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
      LibraryImport::importParts(false, true);
      LibraryImport::importVotes(true);
      LibraryImport::importEvents(true);
    }
}
