<?php

namespace App\Jobs;

use App\LDraw\PartManager;
use App\Models\Part;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DailyMaintenance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Reload all subparts
        Part::lazy()->each(fn (Part $p) => app(PartManager::class)->loadSubpartsFromBody($p));

        // Recount all votes
        Part::unofficial()->lazy()->each(fn (Part $p) => $p->updateVoteData());

        // Find and remove orphan images
        $images = Storage::disk('images')->allFiles('library/unofficial');
        $files = collect($images)
            ->map( function(string $file): string {
                $file = str_replace('_thumb.png', '.png', $file);
                if (strpos($file, 'textures/') !== false) {
                    return str_replace('library/unofficial/', '', $file);
                } else {
                    return str_replace(['library/unofficial/', '.png'], ['', '.dat'], $file);
                }                
            })
            ->unique()
            ->all();
        $in_use_files = Part::unofficial()
            ->whereIn('filename', $files)
            ->pluck('filename')
            ->map( 
                fn (string $filename): string =>
                    strpos($filename, 'textures/') !== false ? "library/unofficial/{$filename}" : str_replace('.dat', '.png', "library/unofficial/{$filename}")
            );
        foreach ($images as $image) {
            if (!$in_use_files->contains($image) && !$in_use_files->contains(str_replace('_thumb.png', '.png', $image))) {
                Storage::disk('images')->delete($image);
            }
        }

        // Find unofficial parts without images and regenerate them
        Part::unofficial()->lazy()->each(function (Part $p) {
            $image = str_replace('dat', '.png', "library/unofficial/{$p->filename}");
            $thumb = str_replace('.png', '_thumb.png', $image);
            if (!Storage::disk('images')->exists($image) || !Storage::disk('images')->exists($thumb)) {
                app(PartManager::class)->updatePartImage($p);
            }
        });

    }
}
