<?php

namespace App\LDraw\Render;

use GdImage;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class LDrawPng
{
    public function __construct(
        public readonly string $tempDisk,
        public readonly string $tempPath,
    ) {}

    public function resizeImage(GdImage $image, int $maxHeight, int $maxWidth): GdImage
    {
        imagesavealpha($image, true);
        $width = imagesx($image);
        $height = imagesy($image);
        $r = $width / $height;
        if ($maxWidth/$maxHeight > $r) {
            $newwidth = $maxHeight * $r;
        } else {
            $newwidth = $maxWidth;
        }
        imagealphablending($image, false);
        $image = imagescale($image, $newwidth);
        imagesavealpha($image, true);
        return $image;
    }

    public function optimize(GdImage $image): GdImage
    {
        imagesavealpha($image, true);
        $filename = Storage::disk($this->tempDisk)->path("{$this->tempPath}/image.png");
        imagepng($image, $filename);
        Process::run("optipng {$filename}");
        $image = imagecreatefrompng($filename);
        imagesavealpha($image, true);
        Storage::disk($this->tempDisk)->delete("{$this->tempPath}/image.png");
        return $image;        
    }
}