<?php

namespace App\LDraw;

use App\Models\Part;
use App\Models\PartCategory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class SupportFiles {
    public static function categoriesText() {
        return implode("\n", PartCategory::all()->pluck('category')->all());
    }

    public static function libaryCsv(): string {
        $csv = "part_number,part_description,part_url,image_url,image_last_modified\n";
        foreach (Part::whereRelation('type', 'folder', 'parts/')->lazy() as $part) {
            if (in_array($part->description[0], ['~','_','|','='])) {
                continue;
            }
            $image = "{$part->libFolder()}/" . substr($part->filename, 0, -4) . '.png';
            $vals = [
                basename($part->filename),
                '"' . str_replace('"', '""', $part->description) . '"',
                route("{$part->libFolder()}.download", $part->filename),
                asset("images/library/{$image}"),
                Carbon::createFromTimestamp(Storage::disk('images')->lastModified("library/{$image}"))->format('Y-m-d')
            ];
            $csv .= implode(',', $vals) . "\n";
        }
        return $csv;
    }
}