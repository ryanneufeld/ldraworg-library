<?php

namespace App\LDraw;

use Illuminate\Database\Eloquent\Collection;

use App\Models\Part;
use App\Models\PartType;
use App\Models\PartHistory;
use App\Models\PartRelease;
use App\Models\PartLicense;
use App\Models\PartEvent;
use App\Models\User;
use App\Models\Vote;
use App\Models\PartCategory;

use App\LDraw\FileUtils;

use App\Jobs\UpdateZip;

class LibraryOperations {
  
  // $file MUST be validated BEFORE using this function
  public static function addFiles($files, User $user, PartType $pt) {
    $parts = new Collection;
    foreach($files as $file) {
      $filename = basename(strtolower($file->getClientOriginalName()));
      
      $upart = Part::findUnofficialByName($pt->folder . $filename);
      $opart = Part::findOfficialByName($pt->folder . $filename);
      $votes_deleted = false;

      // Unofficial file exists
      if (isset($upart)) {
        $init_submit = false;
        if ($upart->isTexmap()) {
          // If the submitter is not the author and has not edited the file before, add a history line
          if ($upart->user_id <> $user->id && empty($upart->history()->whereFirst('user_id', $user->id)))
            PartHistory::create(['user_id' => $user->id, 'part_id' => $upart->id, 'comment' => 'edited']);
          $upart->put($file->get());
        }
        else {
          // Update existing part
          $text = FileUtils::cleanFileText($file->get(), true, true);
          $upart->fillFromText($text);
        }
        if ($upart->votes->count() > 0) $votes_deleted = true;
        Vote::where('part_id', $upart->id)->delete();
        $upart->refresh();
      }
      // Create a new part
      else {
        $init_submit = true;
        if ($file->getMimeType() == 'image/png') {
          // Create a new texmap part
          $upart = Part::createTexmap([
            'user_id' => $user->id,
            'part_release_id' => PartRelease::unofficial(),
            'part_license_id' => PartLicense::defaultLicense()->id,
            'filename' => $pt->folder . $filename,
            'description' => $pt->name . ' ' . $filename,
            'part_type_id' => $pt->id,
          ], $file->get());
        }
        else {            
          // Create a new part
          $text = FileUtils::cleanFileText($file->get(), true, true);
          $upart = Part::createFromText($text, PartRelease::unofficial(), true);
        }  
      }
      
      $upart->updateSubparts(true);
      $upart->updateImage(true);
      
      if (!empty($opart)) {
        $upart->official_part_id = $opart->id;
        $upart->save();
        $opart->unofficial_part_id = $upart->id;
        $opart->save();
      }

      $comment = $filedata['comment'] ?? null;
      PartEvent::createFromType('submit', $user, $upart, $comment, null, null, $init_submit);        

      $parts->add($upart);
      UpdateZip::dispatch($upart->filename, $upart->get());
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
}