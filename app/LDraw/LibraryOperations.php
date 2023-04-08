<?php

namespace App\LDraw;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;


use App\Models\Part;
use App\Models\PartType;
use App\Models\PartHistory;
use App\Models\PartEvent;
use App\Models\User;
use App\Models\Vote;
use App\Models\PartCategory;
use App\Models\PartRelease;
use App\Models\PartBody;

use App\LDraw\FileUtils;

use App\Jobs\UpdateZip;

class LibraryOperations {
  
  // $file MUST be validated BEFORE using this function
  public static function addFiles($files, User $user, PartType $pt, string $comment = null) {
    $parts = new Collection;
    foreach($files as $file) {
      $filename = basename(strtolower($file->getClientOriginalName()));
      $file->storeAs('tmp', $filename, 'library'); 
      $upart = Part::findUnofficialByName($pt->folder . $filename);
      $opart = Part::findOfficialByName($pt->folder . $filename);
      $votes_deleted = false;
      $mpart = Part::findUnofficialByName($filename, true);
      // Unofficial file exists
      if (isset($upart)) {
        $init_submit = false;
        if ($upart->isTexmap()) {
          if ($upart->description == 'Missing') {
            $upart->fillFromFile(Storage::disk('library')->path('tmp/' . $filename), $user, $pt, PartRelease::unofficial());
          }
          else {
            // If the submitter is not the author and has not edited the file before, add a history line
            if ($upart->user_id <> $user->id && empty($upart->history()->whereFirst('user_id', $user->id)))
              PartHistory::create(['user_id' => $user->id, 'part_id' => $upart->id, 'comment' => 'edited']);
            if (is_null($upart->body)) {
              PartBody::create(['part_id' => $upart->id, 'body' => base64_encode(Storage::disk('library')->get('tmp/' . $filename))]);
            }
            else {
              $upart->body->body = base64_encode(Storage::disk('library')->get('tmp/' . $filename));
              $upart->body->save();
            }            
            $upart->put(Storage::disk('library')->get('tmp/' . $filename));
          }
        }
        else {
          // Update existing part
          $text = FileUtils::cleanFileText(Storage::disk('library')->get('tmp/' . $filename), true, true);
          $upart->fillFromText($text, false, PartRelease::unofficial());
        }
        if ($upart->votes->count() > 0) $votes_deleted = true;
        Vote::where('part_id', $upart->id)->delete();
        $upart->refresh();
      }
      // Create a new part
      else {
        $init_submit = true;
        $upart = Part::createFromFile(Storage::disk('library')->path('tmp/' . $filename), $user, $pt, PartRelease::unofficial());
      }
      
      $upart->updateSubparts(true);
      $upart->updateImage(true);
      $upart->saveHeader();
      if (!empty($mpart) && $mpart->description == 'Missing' && $mpart->filename != $upart->filename) {
        $mpart->parents()->each(function (Part $part) { 
          $part->updateSubparts(true);
          $part->updateImage(true); 
        });
      }
      if (!empty($opart)) {
        $upart->official_part_id = $opart->id;
        $upart->save();
        $opart->unofficial_part_id = $upart->id;
        $opart->save();
        Part::unofficial()->whereHas('subparts', function (Builder $query) use ($opart) {
          return $query->where('id', $opart->id);
        })->each(function (Part $part) {
          $part->updateSubparts(true);
        });
      }

      PartEvent::createFromType('submit', $user, $upart, $comment, null, null, $init_submit);        

      $parts->add($upart);
      UpdateZip::dispatch($upart);
      
      Storage::disk('library')->delete('tmp/' . $filename);
    }
    
    return $parts;    
  }
  
  public static function refreshNotifications(): void {
    foreach (User::all() as $user) {
      $parts = Part::whereHas('events', function (Builder $query) use ($user) {
        $query->where('user_id', $user->id);
      })->pluck('id');
      $user->notification_parts()->sync($parts);
    }
  }

  public static function makeMPD(Part $part, bool $unOfficialPriority = false): string {
    $parts = new Collection;
    $part->dependencies($parts, $unOfficialPriority);
    $parts = $parts->diff(new Collection([$part]));
    if ($part->isTexmap()) {
      $model = $part->getFileText();
    }
    else {
      $model = "0 FILE " . $part->name() . "\r\n" . $part->getFileText();
    }  
    foreach ($parts as $p) {
      if ($p->isTexmap()) {
        $model .= "\r\n" . $p->getFileText();
      }
      else {
        $model .= "\r\n0 FILE " . $p->name() . "\r\n" . $p->getFileText();
      }  
    }
    return $model;
  }

  public static function checkOrCreateStandardDirs(string $disk, string $path): void {
    if (!Storage::disk($disk)->exists($path))
      Storage::disk($disk)->makeDirectory($path);
    foreach (config('ldraw.dirs') as $dir) {
      if (!Storage::disk($disk)->exists($path . '/' . $dir))
        Storage::disk($disk)->makeDirectory($path . '/' . $dir);
      if (!Storage::disk($disk)->exists($path . '/' . $dir))
        Storage::disk($disk)->makeDirectory($path . '/' . $dir);
    }
  }

  public static function renderPart(Part $part): void {
    $renderdisk = config('ldraw.ldview.dir.render.disk');
    $renderpath = config('ldraw.ldview.dir.render.path');
    $renderfullpath = realpath(config("filesystems.disks.$renderdisk.root") . '/' . $renderpath);
    $officialimagedisk = config('ldraw.ldview.dir.image.official.disk');
    $officialimagepath = config('ldraw.ldview.dir.image.official.path');
    $officialimagefullpath = realpath(config("filesystems.disks.$officialimagedisk.root") . '/' . $officialimagepath);
    $unofficialimagedisk = config('ldraw.ldview.dir.image.unofficial.disk');
    $unofficialimagepath = config('ldraw.ldview.dir.image.unofficial.path');
    $unofficialimagefullpath = realpath(config("filesystems.disks.$unofficialimagedisk.root") . '/' . $unofficialimagepath);

    // Image saving will fail if these directories do not exist
    self::checkOrCreateStandardDirs($officialimagedisk, $officialimagepath);
    self::checkOrCreateStandardDirs($unofficialimagedisk, $unofficialimagepath);

    $file = $renderpath . '/' . basename($part->filename);
    $contents = $part->get();
    // Fix an LDView quirk with non-part folder parts
    if (!$part->isTexmap() && $part->type->folder != 'parts/') {
      $contents = str_replace(['Unofficial_Subpart', 'Unofficial_Primitive', 'Unofficial_8_Primitive', 'Unofficial_48_Primitive'], 'Unofficial_Part', $contents);
    }
    Storage::disk($renderdisk)->put($file, $contents);
    $filepath = Storage::disk($renderdisk)->path($file);
    if ($part->isTexmap()) {
      $tw = config('ldraw.image.thumb.width');
      $th = config('ldraw.image.thumb.height');
      if ($part->isUnofficial()) {
        $thumbpngfile = $unofficialimagefullpath . '/' . substr($part->filename, 0, -4) . '_thumb.png';        
      }
      else {
        $thumbpngfile = $officialimagefullpath . '/' . substr($part->filename, 0, -4) . '_thumb.png';        
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
      $part->body->body = base64_encode(Storage::disk($renderdisk)->get($file));
      $part->body->save();
      Storage::disk($renderdisk)->delete($file);
    }
    else {
      // LDview requires a p and a parts directory even if empty
      self::checkOrCreateStandardDirs($renderdisk, "$renderpath/ldraw");

      $parts = new Collection;
      $part->dependencies($parts, $part->isUnofficial());
      $parts = $parts->diff(new Collection([$part]));
      foreach ($parts as $p) {
        Storage::disk($renderdisk)->put($renderpath . '/ldraw/' . $p->filename, $p->get());
      }

      if ($part->isUnofficial()) {
        $pngfile = $unofficialimagefullpath . '/' . substr($part->filename, 0, -4) . '.png';
      }
      else {
        $pngfile = $officialimagefullpath . '/' . substr($part->filename, 0, -4) . '.png';
      }
      
      $ldrawdir = $renderfullpath . '/ldraw';
      $ldconfig = realpath(config('filesystems.disks.library.root') . '/official/LDConfig.ldr');
      $ldview = config('ldraw.ldview.path');

      $normal_size = "-SaveWidth=" . config('ldraw.image.normal.width') . " -SaveHeight=" . config('ldraw.image.normal.height');
      $thumb_size = "-SaveWidth=" . config('ldraw.image.thumb.width') . " -SaveHeight=" . config('ldraw.image.thumb.height');
      $thumbfile = substr($pngfile, 0, -4) . '_thumb.png';
      
      $cmds = ['[General]'];
      foreach(config('ldraw.ldview.commands') as $command => $value) {
        $cmds[] = "$command=$value";
      }  
      
      if (array_key_exists($part->basePart(), config('ldraw.ldview.alt-camera'))) {
        $ac = config('ldraw.ldview.alt-camera');
        $cmds[] = " -DefaultLatLong=" . $ac[$part->basePart()];
      }
      Storage::disk($renderdisk)->put("$renderpath/ldview.ini", implode("\n", $cmds));
      $ldviewcmd = "$ldview $filepath -LDConfig=$ldconfig -LDrawDir=$ldrawdir -IniFile=$renderfullpath/ldview.ini $normal_size -SaveSnapshot=$pngfile";
      exec($ldviewcmd);
      exec("optipng $pngfile");
      $ldviewcmd = "$ldview $filepath -LDConfig=$ldconfig -LDrawDir=$ldrawdir -IniFile=$renderfullpath/ldview.ini $thumb_size -SaveSnapshot=$thumbfile";
      exec($ldviewcmd);
      exec("optipng $thumbfile");
      Storage::disk($renderdisk)->deleteDirectory("$renderpath/ldraw");
      Storage::disk($renderdisk)->delete($file);
      Storage::disk($renderdisk)->delete("$renderpath/ldview.ini");
    }
  }
  
  public static function getAllParentIds($part, &$parents, $unofficialOnly = false) {
    if (empty($parents)) $parents = [];
    foreach($part->parents as $parent) {
      if ($unofficialOnly && !$parent->isUnofficial()) continue;
      if (!in_array($parent->id, $parents))
        $parents[] = $parent->id;
      self::getAllParentIds($parent, $parents, $unofficialOnly);
    }
  }

}
