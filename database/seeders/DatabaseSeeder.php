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
      file_put_contents(storage_path('logs/laravel.log'),'');
      Cache::flush();
      $this->call([
          PartCategorySeeder::class,
          PartTypeSeeder::class,
          PartTypeQualifierSeeder::class,
          VoteTypeSeeder::class,
          PartEventTypeSeeder::class,
          PartReleaseSeeder::class,
          PartLicenseSeeder::class,

          PermissionSeeder::class,
          RoleSeeder::class,
          UserSeeder::class,

          PartSeeder::class,
          RelatedPartsSeeder::class,
          VoteSeeder::class,
          PartEventSeeder::class,
      ]);
    }
}
