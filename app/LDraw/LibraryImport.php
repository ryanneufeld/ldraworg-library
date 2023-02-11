<?php

namespace App\LDraw;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Models\Part;
use App\Models\PartType;
use App\Models\PartRelease;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteType;
use App\Models\PartEventType;
use App\Models\PartEvent;
use App\Models\TrackerHistory;

class LibraryImport {
  
  // Current as of 2206
  public static $official_texture_authors = [
    'parts/textures/13710a.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/13710b.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/13710c.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/13710d.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/13710e.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/13710f.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/13710g.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/13710h.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/13710i.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/13710j.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/13710k.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/13710l.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/13710m.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/13710n.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/191764.png' => ['Steffen', '2002'],
    'parts/textures/191767.png' => ['Steffen', '2002'],
    'parts/textures/19201p01.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/19204p01.png' => ['Philippe Hurbain', '2001'],
    'parts/textures/27062p01.png' => ['Philippe Hurbain', '2001'],
    'parts/textures/27062p02.png' => ['Philippe Hurbain', '2001'],
    'parts/textures/36069ap01.png' => ['Philippe Hurbain', '2003'],
    'parts/textures/36069bp01.png' => ['Philippe Hurbain', '2001'],
    'parts/textures/39266p01.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/39266p02.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/39266p03.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/3960p0b.png' => ['Philippe Hurbain', '2201'],
    'parts/textures/4141502a.png' => ['Orion Pobursky', '2202'],
    'parts/textures/4141698a.png' => ['Orion Pobursky', '2202'],
    'parts/textures/47203p01.png' => ['Philippe Hurbain', '2205'],
    'parts/textures/47203p02.png' => ['Philippe Hurbain', '2205'],
    'parts/textures/47203p03.png' => ['Philippe Hurbain', '2203'],
    'parts/textures/47206p01.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/47206p02.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/6005049a.png' => ['Orion Pobursky', '2202'],
    'parts/textures/6022692a.png' => ['Orion Pobursky', '2202'],
    'parts/textures/6022692b.png' => ['Orion Pobursky', '2202'],
    'parts/textures/6022692c.png' => ['Orion Pobursky', '2202'],
    'parts/textures/60581p01.png' => ['Philippe Hurbain', '2205'],
    'parts/textures/6092p01pit1side1.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01pit1side2.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01pit1side3.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01pit1side4.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01pit2corner1.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01pit2corner2.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01pit2corner3.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01pit2side1.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01pit2side2.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01pit2side3.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01pit2side4.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01pit2side5.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01pit2side6.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01top.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01wall1.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01wall2.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01wall3.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01wall4.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01wall5.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01wall6.png' => ['Alex Taylor', '2203'],
    'parts/textures/6092p01wall7.png' => ['Alex Taylor', '2203'],
    'parts/textures/6115204a.png' => ['Marc Giraudet', '2202'],
    'parts/textures/6204380a.png' => ['Vincent Messenet', '2202'],
    'parts/textures/6299663d.png' => ['Evert-Jan Boer', '2003'],
    'parts/textures/6313371a.png' => ['Ulrich Röder', '2202'],
    'parts/textures/66645ap01.png' => ['Philippe Hurbain', '2001'],
    'parts/textures/66645bp01.png' => ['Philippe Hurbain', '2001'],
    'parts/textures/685p01.png' => ['Philippe Hurbain', '2003'],
    'parts/textures/685p02.png' => ['Philippe Hurbain', '2003'],
    'parts/textures/685p03.png' => ['Philippe Hurbain', '2003'],
    'parts/textures/685p05.png' => ['Philippe Hurbain', '2003'],
    'parts/textures/87079pxf.png' => ['Evert-Jan Boer', '2202'],
    'parts/textures/973p5aa.png' => ['Philippe Hurbain', '2205'],
    'parts/textures/973p5ab.png' => ['Philippe Hurbain', '2205'],
    'parts/textures/973paza.png' => ['Philippe Hurbain', '2205'],
    'parts/textures/973pazb.png' => ['Philippe Hurbain', '2205'],
    'parts/textures/98088p01.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/98088p02.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/98088p03.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/98088p04.png' => ['Philippe Hurbain', '2202'],
    'parts/textures/u9480.png' => ['Alex Taylor', '2202'],
    'parts/textures/u9481.png' => ['Alex Taylor', '2202'],
  ];
  
  public static function fixUnofficialHistory() {
    Part::unofficial()->lazy()->each(function($part) {
      $text = FileUtils::cleanFileText(Storage::disk('library')->get('tmp/unofficial/' . $part->filename));
      $history = FileUtils::getHistory($text, true);
      foreach ($history as $h) {
        $hist = \App\Models\PartHistory::where('user_id', $h['user'])->where('part_id', $part->id)->where('comment', $h['comment'])->first();
        $hist->created_at = $h['date'];
        $hist->save();
      }
      $part->saveHeader();
    });
  }

  public static function fixPartQualifiers() {
    Part::lazy()->each( function ($part) {
      if ($part->type->folder == 'parts/') {
        $text = Storage::disk('library')->get('tmp/' . $part->libFolder() . '/' . $part->filename);
        $type = FileUtils::getPartType($text);
        
        if ($type !== false && !empty($type['qual'])) {
          $qual = \App\Models\PartTypeQualifier::firstWhere('type', $type['qual']);
          if (!empty($qual)) {
            
            $part->part_type_qualifier_id = $qual->id;
            $part->save();
            $part->refresh();
            $part->saveHeader();
          }
        }
      }
    });
  }
  public static function importParts($unofficialOnly = false, $updateImages = false) {
    $texusers = self::$official_texture_authors;
    $libs = $unofficialOnly ? ['unofficial'] : ['official','unofficial'];
    foreach ($libs as $lib) {
      foreach (Storage::disk('library')->allDirectories($lib) as $dir) {
        if (strpos($dir,'models') !== false) continue;
        $files = Storage::disk('library')->files($dir);
        foreach ($files as $file) {
          
          if (pathinfo($file, PATHINFO_EXTENSION) != 'dat' && pathinfo($file, PATHINFO_EXTENSION) != 'png') continue;
          if (pathinfo($file, PATHINFO_EXTENSION) == 'dat') {
            $p = Part::createFromFile(Storage::disk('library')->path($file));
          }
          elseif (pathinfo($file, PATHINFO_EXTENSION) == 'png') {
            $f = str_replace("$lib/", '', $file);
            $pt = PartType::firstWhere('folder', pathinfo($f, PATHINFO_DIRNAME) . '/');
            $filename = $pt->folder . basename($file);
            
            if (isset($texusers[$filename][0])) {
              $user = User::findByName($texusers[$filename][0], $texusers[$filename][0]);
            }
            else {
              $user = User::findByName('unknown');
            }
            
            if (isset($texusers[$filename][1])) {
              $rel = PartRelease::firstWhere('short', $texusers[$filename][1]);
            }
            else {
              if ($lib == 'unofficial') {
                $rel = PartRelease::unofficial();
              }
              else {
                $rel = PartRelease::current();
              }
            }
            
            $p = Part::createFromFile(Storage::disk('library')->path($file), $user, $pt, $rel);
            
            unset($user);
            unset($rel);
            unset($pt);
          }
          // Unofficial is processed after official, find official part and associate it.
          if ($lib == 'unofficial' && isset($p)) {
            $opart = Part::findOfficialByName($p->filename);
            if (!empty($opart) ) {
              $p->official_part_id = $opart->id;
              $p->save();
              $opart->unofficial_part_id = $p->id;
              $opart->save();
            }
            unset($opart);
          }
          unset($p);            
        }  
      }  
    }
    //All parts imported, refresh subfile associations and, if set, regenerate images
    foreach (Part::lazy() as $part) {
      $part->updateSubparts();
      if ($updateImages) $part->updateImage();
    }
  }
  
  public static function importVotes($clearTable = false) {
    if ($clearTable) DB::table('votes')->truncate();
    foreach (Storage::disk('library')->allDirectories('unofficial') as $dir) {
      $files = Storage::disk('library')->files($dir);
      foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == 'vote') {
          $partname = substr($file, 11,-5);
          $part = Part::findUnofficialByName($partname);
          $votefile = Storage::disk('library')->get($file);
          $votes = explode("\n", $votefile);
          $fasttrack = (strpos($votefile, 'PTadmin1=certify') !== false && 
                        strpos($votefile, 'PTadmin2=certify') !== false && 
                        (strpos($votefile, 'OrionP=certify') !== false || 
                         strpos($votefile, 'cwdee=certify') !== false));
          foreach ($votes as $vote) {
            if (empty($vote)) continue;
            $v = explode('=', trim($vote));
            if (!isset($v[1]) or $v[1] == 'novote') continue;
            $user = User::findByName($v[0]);
            if (isset($user) and isset($part)) {
              switch($v[1]) {
                case 'certify':
                  if ($user->name == 'OrionP' or $user->name == 'cwdee') {
                    if ($fasttrack) {
                      $code = 'T';
                    }
                    else {
                      $code = 'A';
                    }
                  }
                  else {
                    $code = 'C';
                  }
                  break;
                case 'hold':
                  $code = 'H';
                  break;
              }
              Vote::create(['user_id' => $user->id, 'part_id' => $part->id, 'vote_type_code' => $code]);
            }
          }
        }
      }
    }
    // Update the cached vote count
    foreach (Part::unofficial()->lazy() as $part) {
      $part->updateUncertifiedSubpartCount(true);
    }  
  }

  public static function importEvents($clearTable = false) {
    if ($clearTable) DB::table('part_events')->truncate();
    $eventtypes = PartEventType::all()->pluck('id', 'slug')->all();
    $votetypes = VoteType::all()->pluck('code', 'short')->all();
    $patterns = [
      'date' => '#^At (?<date>\w\w\w \w\w\w \d{1,2} \d\d\:\d\d\:\d\d \d\d\d\d)#um',
      'comment' => '#Comments\:\n(?<comment>.*)$#us',
      'submit_user' => '#^Submitted by\: (?<user>.*?)( proxy=.*?)?$#um',
      'reviewer_user' => '#^Reviewer\: (?<user>.*?)$#um',
      'vote' => '#^Certification\: (?<vote>hold|novote|certify|fasttrack)$#um',
      'rename' => '#part \'(.*)\' was renamed to \'(.*)\'\.$#um',
      'edit' => '#a Parts Tracker Admin edited the header.$#um',
    ];
    foreach (Storage::disk('library')->allDirectories('unofficial') as $dir) {
      if (strpos($dir,'images') !== false) continue;
      $files = Storage::disk('library')->files($dir);
      foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == 'meta') {
          $partname = substr($file, 11,-5);
          $part = Part::findUnofficialByName($partname);
          if (empty($part)) continue;
          $metafile = Storage::disk('library')->get($file);
          $events = explode(str_repeat('=', 70) . "\n", $metafile);
          foreach ($events as $event) {
            if (empty($event)) continue;
            
            $event = trim($event);
            $event = preg_replace('#\R#us', "\n", $event);
            $event = preg_replace('#\n{3,}#us', "\n\n", $event);
            $event = preg_replace('#\h+#us', ' ', $event);
            
            preg_match($patterns['date'], $event, $matches);
            $date = date_format(date_create($matches['date']), 'Y-m-d H:i:s');

            if (preg_match($patterns['submit_user'], $event, $matches)) {
              $eid = $eventtypes['submit'];
              $user = User::findByName(trim($matches['user']), trim($matches['user']));
              if (preg_match($patterns['comment'], $event, $matches)) {
                $comment = $matches['comment'];
              }
              else {
                $comment = null;
              }
              $vc = null;                
            }
            elseif (preg_match($patterns['reviewer_user'], $event, $matches)) {
              $user = User::findByName(trim($matches['user']), trim($matches['user'])) ?? User::findByName('unknown');
              preg_match($patterns['vote'], $event, $matches);
              if ($matches['vote'] == 'novote') {
                $vc = null;
                $eid = $eventtypes['comment'];
              }
              else {                
                $vc = $votetypes[$matches['vote']];
                if ($vc == 'C' && ($user->name == 'OrionP' || $user->name == 'cwdee')) $vc = $votetypes['admincertify'];
                $eid = $eventtypes['review'];
              }
              
              if (preg_match($patterns['comment'], $event, $matches)) {
                $comment = $matches['comment'];
              }
              else {
                $comment = null;
              }  
            }
            elseif (preg_match($patterns['rename'], $event, $matches)) {
              $eid = $eventtypes['rename'];
              $user = User::ptadmin();
              $vc = null;
              $comment = $matches[0];
            }
            elseif (preg_match($patterns['edit'], $event, $matches)) {
              $eid = $eventtypes['edit'];
              $user = User::ptadmin();
              $vc = null;
              $comment = null;
            }
            
            if (!isset($user) || $eid == '') dd($event, $partname);
            PartEvent::create([
              'created_at' => $date,
              'part_event_type_id' => $eid,
              'initial_submit' => null,
              'part_id' => $part->id,
              'user_id' => $user->id,
              'vote_type_code' => $vc,
              'part_release_id' => PartRelease::unofficial()->id,
              'comment' => $comment,
            ]);
            unset($user);
            $eid = '';
          }
          unset($part);
          unset($metafile);
          unset($events);
        }  
      }
    }

    self::updatePartWithEvent();
  }
  
    // Event data can be used to set the authors for unofficial textures
    // update the event initial submit and part created_at
  public static function updatePartWithEvent() {
    DB::table('part_events')->where('part_release_id', PartRelease::unofficial()->id)->update(['initial_submit' => null]);
    $parts = Part::with('events')->unofficial()->lazy();
    foreach ($parts as $part) {
      $sevent = $part->events()->whereRelation('part_event_type', 'slug', 'submit')->oldest()->first();
      $event = $part->events()->oldest()->first();
      if (is_null($sevent)) {
        $part->created_at = $event->created_at;
      }
      else {
        if ($part->isTexmap()) $part->user_id = $sevent->user_id;
        $sevent->initial_submit = true;
        $sevent->save();
        $part->created_at = $sevent->created_at;
      }
      
      $part->save();
      $part->refresh();
      $part->refreshHeader();
    }
  }

  public static function importTrackerHistory(): void {
    $file = Storage::disk('library')->get('tmp/daily.file.counts.log');
    $file = explode("\n", $file);
    foreach ($file as $line) {
      $line = explode("\t", $line);
      $date = \DateTime::createFromFormat('U', $line[0]);
      $data = ['1' => $line[1], '2' => $line[2], '3' => $line[3], '4' => $line[4], '5' => $line[5]];
      $h = new TrackerHistory;
      $h->created_at = $date;
      $h->history_data = $data;
      $h->save();
    }
  }

  public static function savePartBodies() {
    Part::lazy()->each(function ($part) {
      if (!$part->isTexmap()) {
        \App\Models\PartBody::create(['part_id' => $part->id, 'body' => FileUtils::setHeader($part->get(), '')]);
      }
    });
  }
}