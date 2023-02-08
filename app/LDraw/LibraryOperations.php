<?php

namespace App\LDraw;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

use App\Models\Part;
use App\Models\PartType;
use App\Models\PartHistory;
use App\Models\PartEvent;
use App\Models\User;
use App\Models\Vote;
use App\Models\PartCategory;

use App\LDraw\FileUtils;

use App\Jobs\UpdateZip;

use App\Mail\DailyDigest;

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
        $upart = Part::createFromFile(Storage::disk('library')->path('tmp/' . $filename), $user, $pt);
      }
      
      $upart->updateSubparts(true);
      $upart->updateImage(true);
      
      if (!empty($opart)) {
        $upart->official_part_id = $opart->id;
        $upart->save();
        $opart->unofficial_part_id = $upart->id;
        $opart->save();
      }

      PartEvent::createFromType('submit', $user, $upart, $comment, null, null, $init_submit);        

      $parts->add($upart);
      UpdateZip::dispatch($upart->filename, base64_encode($upart->get()));
      
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
}
