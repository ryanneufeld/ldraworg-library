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
        if (file_exists($ldrawdir . '/LDConfig.ldr')) {
          $ldconfig = $ldrawdir . '/LDConfig.ldr';
        }
        else {
          $ldconfig = config('ldraw.officialdir') . '/LDConfig.ldr';
        }  
        $ex001 = config('ldraw.officialdir') . '/parts';
        $ex002 = config('ldraw.officialdir') . '/p';
      }
      else {
        $filepath = config('ldraw.officialdir') . '/' . $this->part->filename;
        $pngfile = config('ldraw.officialimagedir') . '/' . substr($this->part->filename, 0, -4) . '.png';
        $ldrawdir = config('ldraw.officialdir');
        $ldconfig = $ldrawdir . '/LDConfig.ldr'; 
        $ex001 = config('ldraw.unofficialdir') . '/parts';
        $ex002 = config('ldraw.unofficialdir') . '/p';
      }
      
      $ldview = config('ldraw.ldview');

      $normal_size = "-SaveWidth=" . config('ldraw.image.normal.width') . " -SaveHeight=" . config('ldraw.image.normal.height');
      $thumb_size = "-SaveWidth=" . config('ldraw.image.thumb.width') . " -SaveHeight=" . config('ldraw.image.thumb.height');
      $thumbfile = substr($pngfile, 0, -4) . '_thumb.png';
      
      $cmds = '';
      foreach(config('ldraw.ldview_commands') as $command => $value) {
        $cmds .= " -$command=$value";
      }  
      
      $ldviewcmd = "$ldview $filepath -LDConfig=$ldconfig -LDrawDir=$ldrawdir -ExtraSearchDirs/Dir001=$ex001 -ExtraSearchDirs/Dir002=$ex002 $cmds $normal_size -SaveSnapshot=$pngfile";
      exec($ldviewcmd);
      exec("optipng $pngfile");
      $ldviewcmd = "$ldview $filepath -LDConfig=$ldconfig -LDrawDir=$ldrawdir -ExtraSearchDirs/Dir001=$ex001 -ExtraSearchDirs/Dir002=$ex002 $cmds $thumb_size -SaveSnapshot=$thumbfile";
      exec($ldviewcmd);
      exec("optipng $thumbfile");
   }
}
