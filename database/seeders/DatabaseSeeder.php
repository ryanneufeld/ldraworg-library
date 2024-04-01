<?php

namespace Database\Seeders;

use App\LDraw\LibraryConfig;
use App\Models\PartCategory;
use App\Models\PartEventType;
use App\Models\PartLicense;
use App\Models\PartType;
use App\Models\PartTypeQualifier;
use App\Models\VoteType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        PartType::insert(LibraryConfig::partTypes());
        PartTypeQualifier::insert(LibraryConfig::partTypeQualifiers());
        PartLicense::insert(LibraryConfig::partLicenses());
        PartCategory::insert(LibraryConfig::partCategories());
        PartEventType::insert(LibraryConfig::partEventTypes());
        VoteType::insert(LibraryConfig::voteTypes());
    }
}
