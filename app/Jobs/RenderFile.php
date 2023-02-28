<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

use App\Models\Part;
use App\LDraw\LibraryOperations;

class RenderFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private Part $part;
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
      $renderdisk = config('ldraw.ldview.dir.render.disk');
      $renderpath = config('ldraw.ldview.dir.render.path');
      $renderfullpath = realpath(config("filesystems.disks.$renderdisk.root") . '/' . $renderpath);
      $officialimagedisk = config('ldraw.ldview.dir.image.official.disk');
      $officialimagepath = config('ldraw.ldview.dir.image.official.path');
      $officialimagefullpath = realpath(config("filesystems.disks.$officialimagedisk.root") . '/' . $officialimagepath);
      $unofficialimagedisk = config('ldraw.ldview.dir.image.unofficial.disk');
      $unofficialimagepath = config('ldraw.ldview.dir.image.unofficial.path');
      $unofficialimagefullpath = realpath(config("filesystems.disks.$unofficialimagedisk.root") . '/' . $unofficialimagepath);

      $file = $renderpath . '/' . basename($this->part->filename);
      Storage::disk($renderdisk)->put($file, $this->part->get());
      $filepath = Storage::disk($renderdisk)->path($file);
      if ($this->part->isTexmap()) {
        $tw = config('ldraw.image.thumb.width');
        $th = config('ldraw.image.thumb.height');
        if ($this->part->isUnofficial()) {
          $thumbpngfile = $unofficialimagefullpath . '/' . substr($this->part->filename, 0, -4) . '_thumb.png';        
        }
        else {
          $thumbpngfile = $officialimagefullpath . '/' . substr($this->part->filename, 0, -4) . '_thumb.png';        
        }
        list($width, $height) = getimagesize($filepath);
        $r = $width / $height;
        if ($tw/$th > $r) {
            $newwidth = $th*$r;
        } else {
            $newwidth = $tw;
        }
        $png = imagecreatefrompng($filepath);
        imagealphablending($png, false);
        $png = imagescale($png, $newwidth);
        imagesavealpha($png, true);
        imagepng($png, $thumbpngfile);
        exec("optipng $filepath");
        exec("optipng $thumbpngfile");
        $this->part->body->body = base64_encode(Storage::disk($renderdisk)->get($file));
        $this->part->body->save();
        Storage::disk($renderdisk)->delete($file);
      }
      else {
        $parts = new Collection;
        LibraryOperations::dependencies($this->part, $parts, $this->part->isUnofficial());
        $parts = $parts->diff(new Collection([$this->part]));
        foreach ($parts as $p) {
          Storage::disk($renderdisk)->put($renderpath . '/ldraw/' . $p->filename, $p->get());
        }

        if ($this->part->isUnofficial()) {
          $pngfile = $unofficialimagefullpath . '/' . substr($this->part->filename, 0, -4) . '.png';
        }
        else {
          $pngfile = $officialimagefullpath . '/' . substr($this->part->filename, 0, -4) . '.png';
        }
        
        $ldrawdir = $renderfullpath . '/ldraw';
        $ldconfig = realpath(config('filesystems.disks.library.root') . '/official/LDConfig.ldr');
        $ldview = config('ldraw.ldview.path');
  
        $normal_size = "-SaveWidth=" . config('ldraw.image.normal.width') . " -SaveHeight=" . config('ldraw.image.normal.height');
        $thumb_size = "-SaveWidth=" . config('ldraw.image.thumb.width') . " -SaveHeight=" . config('ldraw.image.thumb.height');
        $thumbfile = substr($pngfile, 0, -4) . '_thumb.png';
        
        $cmds = '';
        foreach(config('ldraw.ldview.commands') as $command => $value) {
          $cmds .= " -$command=$value";
        }  
        
        $ldviewcmd = "$ldview $filepath -LDConfig=$ldconfig -LDrawDir=$ldrawdir $cmds $normal_size -SaveSnapshot=$pngfile";
        exec($ldviewcmd);
        exec("optipng $pngfile");
        $ldviewcmd = "$ldview $filepath -LDConfig=$ldconfig -LDrawDir=$ldrawdir $cmds $thumb_size -SaveSnapshot=$thumbfile";
        exec($ldviewcmd);
        exec("optipng $thumbfile");
        Storage::disk($renderdisk)->deleteDirectory("$renderpath/ldraw");
        Storage::disk($renderdisk)->delete($file);
      }

   }
}
