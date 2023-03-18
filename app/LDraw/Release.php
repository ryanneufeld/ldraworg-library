<?php

namespace App\LDraw;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

use App\Models\PartType;
use App\Models\Part;
use App\Models\PartRelease;
use App\Models\PartEvent;
use App\Models\PartHistory;
use App\Models\User;

class Release {
  public static function releaseParts(array $ids, User $user) {
    $next = PartRelease::next();
    $sdisk = config('ldraw.staging_dir.disk');
    $spath = config('ldraw.staging_dir.path');
    $data = self::getReleaseData($ids);
    //Storage::disk($sdisk)->put("$spath/ldraw/models/Note" . $next['short'] . "CA.txt", $note);
    $release = PartRelease::create(['name' => $next['name'], 'short' => $next['short'], 'part_data' => $data]);
    $partslist = [];
    foreach (Part::whereIn('id', $ids)->lazy() as $part) {
      // Update release for event released parts
      PartEvent::whereRelation('release', 'short', 'unof')->where('part_id', $part->id)->update(['part_release_id' => $release->id]);

      // Post a release event     
      PartEvent::createFromType('release', $user, $part, 'Release ' . $release->name, null, $release);

      // Add history line
      PartHistory::create(['user_id' => $user->id, 'part_id' => $part->id, 'comment' => 'Official Update ' . $release->name]);
      $part->refreshHeader();
      
      // Remove the votes associated with the part
      foreach ($part->votes as $vote) {
        $vote->delete();
      }
      
      // Part is an official update
      if (!is_null($part->official_part_id)) {
        $opart = Part::find($part->official_part_id);
        $text = $part->get();

        // Update the official part
        if ($opart->isTexmap()) {
          $opart->body->body = $part->get();
          $opart->body->save();
          foreach($opart->history() as $h) {
            $h->delete();
          }
          foreach($part->history()->latest()->get() as $h) {
            PartHistory::create(['created_at' => $h->created_at, 'user_id' => $h->user_id, 'part_id' => $opart->id, 'comment' => $h->comment]);
          }
        } 
        else {
          $opart->fillFromText($text, false, $release);
        }
        $opart->unofficial_part_id = null;
        $opart->save();

        // Update events with official part id
        PartEvent::where('part_release_id', $release->id)->where('part_id', $part->id)->update(['part_id' => $opart->id]);

        $part->delete();
      }
      // Part is a new part
      else {
        // Make unofficial part official
        $part->release()->associate($release);
        $part->notification_users()->sync([]);
        $part->refreshHeader();
        $part->vote_sort = 1;
        $part->vote_summary = null;
        $part->uncertified_subpart_count = 0;
        $part->save();

        // Update parts list
        if ($part->type->folder == 'parts/') {
          $partslist[] = [$part->description, $part->filename];
          $f = substr($part->filename, 0, -4);
          if ($part->isTexmap()) {
            Storage::disk('images')->put("library/updates/view{$release->short}/" . $part->filename, $part->get());
          }
          elseif (Storage::disk('images')->exists("library/unofficial/$f.png")) {
            Storage::disk('images')->copy("library/unofficial/$f.png", "library/updates/view{$release->short}/$f.png");
          }
          if (Storage::disk('images')->exists("library/unofficial/{$f}_thumb.png"))
            Storage::disk('images')->copy("library/unofficial/{$f}_thumb.png", "library/updates/view{$release->short}/{$f}_thumb.png");
        }
          
      }
    }
    usort($partslist, function(array $a, array $b) { return $a[0] <=> $b[0]; });
    $release->part_list = $partslist;
    $release->save();
  }

  public static function getReleaseData(array $ids): array {
    $next = PartRelease::next();
    $data = [];
    $data['total_files'] = Part::whereIn('id', $ids)->count();
    $data['new_files'] = Part::whereIn('id', $ids)->where('official_part_id', null)->count();
    $data['new_types'] = [];
    foreach (PartType::all() as $type) {
      if ($type->type == "Part") {
        $count = Part::whereIn('id', $ids)->where('official_part_id', null)->where(function (Builder $query) use ($type) {
          $query->orWhere('part_type_id', $type->id)->orWhere('part_type_id', PartType::firstWhere('type', 'Shortcut')->id);
        })->count();
      }
      elseif ($type->type == "Shortcut") {
        continue;
      }
      else {
        $count = Part::whereIn('id', $ids)->where('official_part_id', null)->where('part_type_id', $type->id)->count();
      } 
      if ($count > 0) $data['new_types'][] = ['name' => $type->name, 'count' => $count];
    }
    $data['moved_parts'] = [];
    foreach (Part::whereIn('id', $ids)->whereRelation('category', 'category', 'Moved')->get() as $part) {
      $data['moved_parts'][] = ['name' => $part->name(),  'movedto' => $part->description]; 
    }
    $data['fixes'] = [];
    $data['rename'] = [];
    foreach (Part::whereIn('id', $ids)->where('official_part_id', '<>', null)->whereRelation('category', 'category', '<>', 'Moved')->get() as $part) {
      $op = Part::find($part->official_part_id);
      if ($part->description != $op->description) {
        $data['rename'][] = ['name' => $part->name(), 'decription' => $part->description, 'old_description' => $op->description];
      }
      else {
        $data['fixed'][] = ['name' => $part->name(), 'decription' => $part->description];
      }
    }
    return $data;
  }
  public static function makeNotes(PartRelease $r): string {
    $data = $r->part_data;
    $notes = "ldraw.org Parts Update " . $r->name . "\n" . 
      str_repeat('-', 76) . "\n\n" .
      "Redistributable Parts Library - Core Library\n" . 
      str_repeat('-', 76) . "\n\n" .
      "Notes created " . date_format($r->created_at, "r"). " by the Parts Tracker\n\n" .
      "Release statistics:\n" . 
      "   Total files: " . $data['total_files'] . "\n" . 
      "   New files: " . $data['new_files'] . "\n";
    foreach ($data['new_types'] as $t) {
      $notes .= "   New " . $t['name'] . "s:" . $t['count'] . "\n";
    }
    $notes .= "\n" . 
      "Moved Parts\n";
    foreach ($data['moved_parts'] as $m) {
      $notes .= '   ' . $m['name'] . str_repeat(' ', 27 - strlen($m['name'])) . $m['movedto']. "\n"; 
    }
    $notes .= "\n" . 
      "Renamed Parts\n";
    foreach ($data['rename'] as $m) {
      $notes .= '   ' . $m['name'] . str_repeat(' ', 27 - strlen($m['name'])) . $m['old_decription'] . "\n" .
      "   changed to    ". $m['decription'] ."\n";    }  
    $notes .= "\n" . 
      "Other Fixed Parts\n";
    foreach ($data['fixes'] as $m) {
      $notes .= '   ' . $m['name'] . str_repeat(' ', 27 - strlen($m['name'])) . $m['decription'] . "\n";
    }
    return $notes;      
  }

}