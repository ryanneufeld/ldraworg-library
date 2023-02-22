<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

use App\Models\Part;
use App\Models\PartType;
use App\Models\PartRelease;
use App\Models\PartEvent;
use App\Models\PartHistory;

use App\LDraw\LDrawFileValidate;
use App\LDraw\FileUtils;

class ReleaseController extends Controller
{
  public function index(Request $request)
  {
    if ($request->has('latest') && $request->get('latest') == true) {
      $releases = PartRelease::current();
    }
    else {
      $releases = PartRelease::where('short', '<>', 'unof')->latest()->get();
    }
    return view('tracker.release.index', ['releases' => $releases , 'latest' => $request->get('latest') == true]);
  }

  public function create(Request $request, $step = null)
    {
      $this->authorize('create', PartRelease::class);
      switch($step) {
        case 3:
          $validated = $request->validate([
            'ids.*' => 'required|integer',
            'approve' => 'required:accepted',
          ]);
          $this->doStep3($validated['ids'], $request->file('ldrawfile'));
          break;
        case 2:
          //dd($request->file('ldrawfiles')[0]->getClientOriginalName());
          $validated = $request->validate([
            'ldrawfiles.*' => 'sometimes|file',
            'ids.*' => 'required|integer',
          ]);
          return $this->doStep2($validated['ids'], $validated['ldrawfiles'] ?? []);
          break;
        case 1:
        default:
          return $this->doStep1();
          break;
      }
    }
    
  protected function doStep1() {
    $parts = Part::unofficial()->where('vote_sort', 1)->orderBy('part_type_id')->get();
    $results = [];
    foreach($parts as $part) {
      $text = $part->isTexmap() ? $part->header : $part->get();
      $errors = LDrawFileValidate::ValidName($text, basename($part->filename), $part->part_type_id);
    
      // These checks are only valid for non-texmaps
      if (!$part->isTexmap()) {
        $errors = array_merge($errors, LDrawFileValidate::ValidDescription($text));
        $errors = array_merge($errors, LDrawFileValidate::ValidAuthor($text));
        $errors = array_merge($errors, LDrawFileValidate::ValidPartType($text, $part->part_type_id));
        $errors = array_merge($errors, LDrawFileValidate::ValidCategory($text));
        $errors = array_merge($errors, LDrawFileValidate::ValidKeywords($text));
        $errors = array_merge($errors, LDrawFileValidate::ValidHistory($text));
        $errors = array_merge($errors, LDrawFileValidate::ValidLines($text));      
      }
      $warnings = LDrawFileValidate::historyEventsCrossCheck($part);      
      if (isset($part->category) && $part->category->category == "Minifig") {
        $warnings[] = "Check Minifig category: {$part->category->category}";
      }
      
      $results[] = ['part' => $part, 'release' => empty($errors) && $part->releasable(), 'errors' => $errors, 'warnings' => $warnings];
    }
    return view('tracker.release.create.step1', ['parts' => $results]);
  }

  protected function doStep2($ids, $ldrawfiles = []) {
    $notes = $this->makeNotes($ids);
    $r = PartRelease::next();
    $this->makeZipFiles($r, $notes, $ids, $ldrawfiles);
    $zips = ['update' => 'library/updates/staging/lcad'. $r['short'] . '.zip', 'complete' => 'library/updates/staging/completeCA.zip'];
    return view('tracker.release.create.step2', ['notes' => $notes, 'ids' => $ids, 'zips' => $zips]);
  }

  protected function doStep3($ids) {
    $next = PartRelease::next();

    $zip = new \ZipArchive;
    $zip->open(storage_path('app/library/updates/staging/lcad'. $next['short'] . '.zip'));
    $note = $zip->getFromName('ldraw/models/Note' . $next['short'] . 'CA.txt', 0, \ZipArchive::FL_NOCASE);
    $zip->close();

    $release = PartRelease::create(['name' => $next['name'], 'short' => $next['short'], 'notes' => $note]);

    Part::whereIn('id', $ids)->lazy()->each(function (Part $part) use ($release) {
      $part->events()->whereRelation('release', 'short', 'unof')->each(function (PartEvent $event) use ($release) {
        $event->release()->associate($release);
        $event->save();
      });
      
      PartEvent::createFromType('release', Auth::user(), $part, 'Release ' . $release->name, null, $release);

      if (!is_null($part->official_part_id)) {
        // Update the official part
        $opart = Part::find($part->official_part_id);
        $opart->fillFromText(rtrim($part->header) . "/n/n" . $part->body->body, false, $release);
        PartHistory::create(['user_id' => Auth::user()->id, 'part_id' => $opart->id, 'comment' => 'Official Update ' . $release->name]);
        //Remove the unofficial part
        $part->delete();
      }
      else {
        // Make unofficial part official
        PartHistory::create(['user_id' => Auth::user()->id, 'part_id' => $part->id, 'comment' => 'Official Update ' . $release->name]);
        $part->release()->associate($release);
        $part->notification_users()->sync([]);
        $part->save();
        $part->refresh();
        $part->refreshHeader();
        $part->put(rtrim($part->header) . "/n/n" . $part->body->body);
      }
    });
    return view('tracker.release.create.step3');
  }
    
  protected function makeNotes($ids) {
    $next = PartRelease::next();
    $notes = "ldraw.org Parts Update " . $next['name'] . "\n" . 
      str_repeat('-', 76) . "\n\n" .
      "Redistributable Parts Library - Core Library\n" . 
      str_repeat('-', 76) . "\n\n" .
      "Notes created " . date_format(date_create(), "r"). " by the Parts Tracker\n\n" .
      "Release statistics:\n" . 
      "   Total files: " . Part::whereIn('id', $ids)->count() . "\n" . 
      "   New files: " . Part::whereIn('id', $ids)->where('official_part_id', null)->count() . "\n";
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
      if ($count > 0) $notes .= "   New " . strtolower($type->name) . "s: $count\n";
    }
    $notes .= "\n" . 
      "Moved Parts\n";
    foreach (Part::whereIn('id', $ids)->whereRelation('category', 'category', 'Moved')->get() as $part) {
      $notes .= '   ' . $part->name() . str_repeat(' ', 27 - strlen($part->name())) . "{$part->description}\n"; 
    }
    $rename = '';
    $fixes = '';
    foreach (Part::whereIn('id', $ids)->where('official_part_id', '<>', null)->get() as $part) {
      $op = Part::find($part->official_part_id);
      if ($part->description != $op->description) {
        $rename .= '   ' . $part->name() . str_repeat(' ', 27 - strlen($part->name())) . "{$op->description}\n" .
          "   changed to    {$part->description}\n";
      }
      else {
        $fixes .= '   ' . $part->name() . str_repeat(' ', 27 - strlen($part->name())) . "{$part->description}\n";
      }
    }
    $notes .= "\nRenamed Parts\n$rename\nOther Fixed Parts\n$fixes";
    return $notes;      
  }
  
  protected function makeZipFiles($release, $notes, $ids, $ldrawfiles = []) {
    $uzip = new \ZipArchive;
    $uzip->open(storage_path('app/library/updates/staging/lcad'. $release['short'] . '.zip'), \ZipArchive::CREATE);

    Storage::disk('library')->copy('updates/complete.zip','updates/staging/completeCA.zip');
    $zip = new \ZipArchive;
    $zip->open(storage_path('app/library/updates/staging/completeCA.zip'));
    set_time_limit(60);

    // Create update zip and update complete zip
    foreach(Part::whereIn('id', $ids)->get() as $part) {      
      if($part->isTexmap()) {
        $content = base64_decode($part->body->body);
      }
      else {
        $content = rtrim($part->header);

        // Replae type with release type line
        $utype = '0 !LDRAW_ORG Unofficial_' . $part->type->type;
        $rtype = '0 !LDRAW_ORG ' . $part->type->type . ' UPDATE ' . $release['name'];
        $content = str_replace($utype, $rtype, $content);

        // Add release history line
        if (stripos($content, '!HISTORY') === false) $content .= "\n";
        $content .= "\n0 !HISTORY " . date_format(date_create(), "Y-m-d") . " [" . Auth::user()->name . "] Official Update " . $release['name'];

        //Dos line endings
        $content = FileUtils::unix2dos($content . "\n\n" . $part->body->body);
      }
      $uzip->addFromString('ldraw/' . $part->filename, $content);
      $zip->addFromString('ldraw/' . $part->filename, $content);
    }

    // Add the notes file
    $uzip->addFromString('ldraw/models/Note' . $release['short'] . 'CA.txt', $notes);
    $zip->addFromString('ldraw/models/Note' . $release['short'] . 'CA.txt', $notes);
    
    // Add updated files for the base folder
    foreach($ldrawfiles as $file) {
      $uzip->addFromString('ldraw/' . $file->getClientOriginalName(), $file->get());
      $zip->addFromString('ldraw/' . $file->getClientOriginalName(), $file->get());  
    }

    $zip->close();
    $uzip->close();
  }
}
