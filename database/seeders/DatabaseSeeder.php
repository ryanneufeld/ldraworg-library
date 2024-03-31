<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
      $this->call([
        PartCategorySeeder::class,
        PartTypeSeeder::class,
        PartTypeQualifierSeeder::class,
        VoteTypeSeeder::class,
        PartEventTypeSeeder::class,
        PartLicenseSeeder::class,
        PermissionSeeder::class,
      ]);
    }
}
