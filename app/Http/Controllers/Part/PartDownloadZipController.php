<?php

namespace App\Http\Controllers\Part;

use App\Http\Controllers\Controller;
use App\Models\Part;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class PartDownloadZipController extends Controller
{
    public function __invoke(Part $part)
    {
        if ($part->type->folder !== 'parts/') {
            return response()->redirectToRoute('unofficial.download', $part->filename);
        }
        $dir = TemporaryDirectory::make()->deleteWhenDestroyed();
        $zip = new \ZipArchive;
        $name = basename($part->filename, '.dat').'.zip';
        $zip->open($dir->path($name), \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($part->isUnofficial()) {
            $zipparts = $part->descendantsAndSelf()->doesntHave('unofficial_part')->get();
        } else {
            $zipparts = $part->descendantsAndSelf()->whereNotNull('part_release_id')->get();
        }
        $zipparts->each(function (Part $part) use ($zip) {
            $zip->addFromString($part->filename, $part->get());
        });
        $zip->close();
        $contents = file_get_contents($dir->path($name));

        return response()->streamDownload(function () use ($contents) {
            echo $contents;
        },
            $name,
            [
                'Content-Type' => 'application/zip',
            ]);
    }
}
