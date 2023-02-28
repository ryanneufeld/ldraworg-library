<?php

namespace App\LDraw;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
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
  
  public static function ptreleases($output, $type, $fields) {
    if ($output != 'XML' && $output != 'TAB') $output = 'XML';
    if (!in_array($type, ['ANY','ZIP','ARJ'])) $type = 'ANY';
    $fields = explode('-', $fields);
  }

  public static function categoriesText() {
    return implode("\n", PartCategory::all()->pluck('category')->all());
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
    self::dependencies($part, $parts, $unOfficialPriority);
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

  public static function dependencies(Part $part, Collection $parts, bool $unOfficialPriority = false): void {
    if(!$parts->contains($part)) {
      $parts->add($part);
    }
    foreach ($part->subparts as $spart) {
      if ($unOfficialPriority && !$spart->isUnofficial() && !is_null($spart->unofficial_part_id)) {
        self::dependencies(Part::find($spart->unofficial_part_id), $parts, $unOfficialPriority);
      }
      else {
        self::dependencies($spart, $parts, $unOfficialPriority);
      }
    }
  }
  
  public static function baselineComplete() {
    $zip = new \ZipArchive;
    $zip->open(storage_path('app/library/updates/completeBase.zip'), \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
    $zip->addFromString('test.txt', 'test');
    $zip->close();
    Part::official()->chunk(500, function (Collection $parts) use ($zip) {
      $zip->open(storage_path('app/library/updates/completeBase.zip'));
      foreach($parts as $part) {
        $zip->addFromString('ldraw/' . $part->filename, $part->get());
      }
      $zip->close();
    });
    $zip->open(storage_path('app/library/updates/completeBase.zip'));
    foreach (Storage::disk('library')->allFiles('official') as $filename) {
      $zip->addFromString('ldraw/' . str_replace('official/', '', $filename), Storage::disk('library')->get($filename));
    }
    $zip->deleteName('test.txt');
    $zip->close();
  }
}
