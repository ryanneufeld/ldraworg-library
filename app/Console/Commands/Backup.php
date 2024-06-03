<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Backup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lib:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes a backup package';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $zip = new \ZipArchive();
        if (!Storage::disk('local')->directoryExists('backup')) {
            Storage::disk('local')->makeDirectory('backup');
        }
        $zipname = Storage::disk('local')->path('backup/backup.zip');

        $zip->open($zipname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $zip->addFile(base_path('.env'), '.env');

        foreach(Storage::disk('local')->allFiles('assets') as $file) {
            $zip->addFile(Storage::disk('local')->path($file), "storage/app/{$file}");
        }
        
        foreach(Storage::disk('local')->allFiles('deleted') as $file) {
            $zip->addFile(Storage::disk('local')->path($file), "storage/app/{$file}");
        }

        foreach(Storage::disk('images')->allFiles('banners') as $file) {
            $zip->addFile(Storage::disk('images')->path($file), "storage/app/images/{$file}");
        }
        foreach(Storage::disk('images')->allFiles('cards') as $file) {
            $zip->addFile(Storage::disk('images')->path($file), "storage/app/images/{$file}");
        }
        foreach(Storage::disk('images')->allFiles('updates') as $file) {
            $zip->addFile(Storage::disk('images')->path($file), "storage/app/images/{$file}");
        }
        $zip->addFile(Storage::disk('images')->path('LDraw_Black_64x64'), 'storage/app/images/LDraw_Black_64x64');
        $zip->addFile(Storage::disk('images')->path('LDraw_Green_64x64'), 'storage/app/images/LDraw_Green_64x64');
        $zip->addFile(Storage::disk('images')->path('LDraw_Orange_64x64'), 'storage/app/images/LDraw_Orange_64x64');

        foreach(Storage::disk('library')->allFiles('official') as $file) {
            $zip->addFile(Storage::disk('library')->path($file), "storage/app/library/{$file}");
        }
        $zip->close();
        $zip->open($zipname);
        foreach(Storage::disk('library')->allFiles('omr') as $file) {
            $zip->addFile(Storage::disk('library')->path($file), "storage/app/library/{$file}");
        }
        $zip->close();
        $zip->open($zipname);
        foreach(Storage::disk('library')->allFiles('updates') as $file) {
            $zip->addFile(Storage::disk('library')->path($file), "storage/app/library/{$file}");
        }
        $zip->close();
    }
}
