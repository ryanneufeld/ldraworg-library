<?php

namespace App\Console\Commands;

use App\LDraw\LibraryConfig;
use App\Models\PartCategory;
use App\Models\PartEventType;
use App\Models\PartLicense;
use App\Models\PartType;
use App\Models\PartTypeQualifier;
use App\Models\VoteType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class UpdateConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lib:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update or refresh the configuration values for the library';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (LibraryConfig::partLicenses() as $license) {
            PartLicense::updateOrCreate(
                ['name' => $license['name']],
                $license
            );
        }

        foreach (LibraryConfig::partTypes() as $type) {
            PartType::updateOrCreate(
                ['type' => $type['type']],
                $type
            );
        }

        foreach (LibraryConfig::partTypeQualifiers() as $type) {
            PartTypeQualifier::updateOrCreate(
                ['type' => $type['type']],
                $type
            );
        }

        foreach (LibraryConfig::partCategories() as $category) {
            PartCategory::updateOrCreate(
                ['category' => $category['category']],
                $category
            );
        }

        foreach (LibraryConfig::partEventTypes() as $et) {
            PartEventType::updateOrCreate(
                ['slug' => $et['slug']],
                $et
            );
        }

        foreach (LibraryConfig::voteTypes() as $vt) {
            VoteType::updateOrCreate(
                ['code' => $vt['code']],
                $vt
            );
        }

        foreach (PartType::getDirectories() as $dir) {
            $dir = substr($dir, 0, -1);
            if (! Storage::disk('images')->exists("library/official/{$dir}")) {
                Storage::disk('images')->makeDirectory("library/official/{$dir}");
            }
            if (! Storage::disk('images')->exists("library/unofficial/{$dir}")) {
                Storage::disk('images')->makeDirectory("library/unofficial/{$dir}");
            }
        }

        if (! Storage::disk('images')->exists('library/updates')) {
            Storage::disk('images')->makeDirectory('library/updates');
        }

    }
}
