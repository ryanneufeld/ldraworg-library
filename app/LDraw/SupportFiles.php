<?php

namespace App\LDraw;

use App\Models\Part;
use App\Models\PartCategory;

class SupportFiles {
    public static function categoriesText() {
        return implode("\n", PartCategory::all()->pluck('category')->all());
    }

    public static function libaryCsv(): string {
        $csv = "part_number,part_description,part_url,image_url\n";
        foreach (Part::whereRelation('type', 'folder', 'parts/')->lazy() as $part) {
            if (in_array($part->description[0], ['~','_','|','='])) {
                continue;
            } 
            $num = basename($part->filename);
            $csv .= "{$num},{$part->description}," . route("{$part->libFolder()}.download", $part->filename) . "," . asset("images/library/{$part->libFolder()}/" . substr($part->filename, 0, -4) . '.png') . "\n";
        }
        return $csv;
    }
}