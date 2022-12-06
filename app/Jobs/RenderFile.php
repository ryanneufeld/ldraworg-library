<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Models\Part;

class RenderFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $part;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Part $part)
    {
      $this->part = $part;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      if ($this->part->unofficial) {
        $filepath = config('ldraw.unofficialdir') . '/' . $this->part->filename;
        $pngfile = config('ldraw.unofficialimagedir') . '/' . substr($this->part->filename, 0, -4) . '.png';
        $ldrawdir = config('ldraw.unofficialdir');
        $ex001 = config('ldraw.officialdir') . '/parts';
        $ex002 = config('ldraw.officialdir') . '/p';
      }
      else {
        $filepath = config('ldraw.officialdir') . '/' . $this->part->filename;
        $pngfile = config('ldraw.officialimagedir') . '/' . substr($this->part->filename, 0, -4) . '.png';
        $ldrawdir = config('ldraw.officialdir');
        $ex001 = config('ldraw.unofficialdir') . '/parts';
        $ex002 = config('ldraw.unofficialdir') . '/p';
      }
      $ldconfig = config('ldraw.officialdir') . '/LDConfig.ldr'; 
      $ldview = config('ldraw.ldview');
      $extracommands = "-SaveWidth=300 -SaveHeight=300 -Texmaps=1 -AutoCrop=1 -BackgroundColor3=0xFFFFFF -BFC=0 -ConditionalHighlights=1 -FOV=0.1 -LineSmoothing=1 -MemoryUsage=0";
      $extracommands .= "-ProcessLDConfig=1 -SaveAlpha=1 -SaveZoomToFit=1 -SeamWidth=0 -ShowHighlightLines=1 -SubduedLighting=1 -UseQualityStuds=1 -UseSpecular=0 -DebugLevel=0";
      $extracommands .= "-CheckPartTracker=0 -LightVector=-1,1,1 -TextureStuds=0";      
      $ldviewcmd = "$ldview $filepath -LDConfig=$ldconfig -LDrawDir=$ldrawdir -ExtraSearchDirs/Dir001=$ex001 -ExtraSearchDirs/Dir002=$ex002 $extracommands -SaveSnapshot=$pngfile";
      Log::debug($ldviewcmd);
      exec($ldviewcmd);
      exec("optipng $pngfile");
    }
}
