<?php

namespace App\LDraw\Render;

use App\LDraw\LDrawModelMaker;
use App\LDraw\Parse\Parser;
use App\Models\Omr\OmrModel;
use App\Models\Part;
use GdImage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class LDView
{
    public function __construct(
        public readonly array $options,
        public readonly array $altCameraPositions,
        public readonly string $tempDisk,
        public readonly string $tempPath,
        public readonly string $ldconfigPath,
        public readonly int $maxHeight,
        public readonly int $maxWidth,
        public readonly bool $debug,
        public LDrawModelMaker $modelmaker,
    ) {}
    
    public function render(Part|OmrModel $part): GDImage
    {
        // LDview requires a p and parts directory
        Storage::disk($this->tempDisk)->makeDirectory("{$this->tempPath}/ldraw/parts");
        Storage::disk($this->tempDisk)->makeDirectory("{$this->tempPath}/ldraw/p");
        $ldrawdir = Storage::disk($this->tempDisk)->path("{$this->tempPath}/ldraw");
        
        // Store the part as an mpd
        $filename = "{$this->tempPath}/part.mpd";
        if ($part instanceof Part && array_key_exists(basename($part->filename, '.dat'), $this->altCameraPositions)) {
            $matrix = $this->altCameraPositions[basename($part->filename, '.dat')];
        } elseif ($part instanceof Part && array_key_exists($part->basePart(), $this->altCameraPositions)) {
            $matrix = $this->altCameraPositions[$part->basePart()];
        } else {
            $matrix = "1 0 0 0 1 0 0 0 1";
        }
        if ($part instanceof Part) {
            Storage::disk($this->tempDisk)->put($filename, $this->modelmaker->partMpd($part, $matrix));
        } else {
            Storage::disk($this->tempDisk)->put($filename, $this->modelmaker->modelMpd($part));
        }
        
        $filepath = Storage::disk($this->tempDisk)->path($filename);
        
        $normal_size = "-SaveWidth={$this->maxWidth} -SaveHeight={$this->maxWidth}";
        $imagepath = Storage::disk($this->tempDisk)->path("{$this->tempPath}/part.png");
        
        // Make the ldview.ini
        $cmds = ['[General]'];
        foreach($this->options as $command => $value) {
          $cmds[] = "{$command}={$value}";
        }  
        Storage::disk($this->tempDisk)->put("{$this->tempPath}/ldview.ini", implode("\n", $cmds));
        $inipath = Storage::disk($this->tempDisk)->path("{$this->tempPath}/ldview.ini");
        
        // Run LDView
        $ldviewcmd = "ldview {$filepath} -LDConfig={$this->ldconfigPath} -LDrawDir={$ldrawdir} -IniFile={$inipath} {$normal_size} -SaveSnapshot={$imagepath}";
        if ($this->debug) {
            Log::debug($ldviewcmd);
        }

        Process::run($ldviewcmd);
        $png = imagecreatefrompng($imagepath);
        imagesavealpha($png, true);
        // Remove temp files
        Storage::disk($this->tempDisk)->deleteDirectory("{$this->tempPath}/ldraw");
        Storage::disk($this->tempDisk)->delete("{$this->tempPath}/part.mpd");
        Storage::disk($this->tempDisk)->delete("{$this->tempPath}/part.png");
        Storage::disk($this->tempDisk)->delete("{$this->tempPath}/ldview.ini");
        return $png;
    }
}