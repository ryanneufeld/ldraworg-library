<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

use App\Models\Part;
use App\Models\PartType;
use App\Models\PartRelease;

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
            'ldrawfile.*' => 'sometimes|required|file',
            'ids.*' => 'required|integer',
            'approve' => 'required:accepted',
          ]);
          $this->doStep3($validated['ids'], $request->file('ldrawfile'));
          break;
        case 2:
          $validated = $request->validate([
            'ids.*' => 'required|integer',
          ]);
          return $this->doStep2($validated['ids']);
          break;
        case 1:
        default:
          return $this->doStep1();
          break;
      }
    }
    
  protected function doStep1() {
    $parts = Part::where('vote_sort', 1)->orderBy('part_type_id')->get();
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

  protected function doStep2($ids) {
    $notes = $this->makeNotes($ids);            
    return view('tracker.release.create.step2', ['notes' => $notes, 'ids' => $ids]);
  }

  protected function doStep3($ids, $ldrawfiles) {
    $notes = $this->makeNotes($ids);
    $next = PartRelease::next();
    $release = PartRelease::create(['name' => $next['name'], 'short' => $next['short'], 'notes' => $notes]);
    foreach(Part::whereIn('id', $ids)->lazy() as $part) {
      $part->part_release_id = $release->id;
      $part->refresh();
      $part->updateHeaderFromDB();
      if (!is_null($part->official_part_id)) {
        $op = Part::find($part->official_part_id);
        if ($part->isTexmap()) {
          $op->header = $part->header;
          $op->fillFromText($part->get(), null, false, true);
        }
        else {
          $op->fillFromText($part->get(), null, true);
        }
        $op->unofficial_part_id = null;
        $op->save();
        $part->delete();
      }
      else {
        Storage::disk('library')->move('unofficial/' . $part->filename, 'official/' . $part->filename);
        $part->vote_sort = 1;
        $part->vote_summary = null;
        $part->uncertified_subparts = 0;
      }
    }
    $this->makeZipFiles($release, $notes, $ldrawfiles);
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
      $notes .= '   ' . $part->nameString() . str_repeat(' ', 27 - strlen($part->nameString())) . "{$part->description}\n"; 
    }
    $rename = '';
    $fixes = '';
    foreach (Part::whereIn('id', $ids)->where('official_part_id', '<>', null)->get() as $part) {
      $op = Part::find($part->official_part_id);
      if ($part->description != $op->description) {
        $rename .= '   ' . $part->nameString() . str_repeat(' ', 27 - strlen($part->nameString())) . "{$op->description}\n" .
          "   changed to    {$part->description}\n";
      }
      else {
        $fixes .= '   ' . $part->nameString() . str_repeat(' ', 27 - strlen($part->nameString())) . "{$part->description}\n";
      }
    }
    $notes .= "\nRenamed Parts\n$rename\nOther Fixed Parts\n$fixes";
    return $notes;      
  }
  
  protected function makeZipFiles($release, $notes, $ldrawfiles = []) {
    $uzip = new \ZipArchive;
    $uzip->open(storage_path('app/library/updates/staging/lcad'. $release->short . '.zip'), \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

    $zip = new \ZipArchive;
    $zip->open(storage_path('app/library/updates/staging/completeCA.zip'), \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
    
    $parts = Part::where('part_release_id', $release->id)->pluck('filename')->all();
    $dirs = Storage::disk('library')->allDirectories('official');
    foreach(Storage::disk('library')->allDirectories('official') as $dir) {
      foreach(Storage::disk('library')->files($dir) as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) != 'dat' && pathinfo($file, PATHINFO_EXTENSION) != 'png') continue;
        $contents = Storage::disk('library')->get($file);
        if (pathinfo($file, PATHINFO_EXTENSION) == 'dat') $contents = FileUtils::unix2dos($contents);
        $loc = str_replace('official/', 'ldraw/', $file);
        $zip->addFromString($loc, $contents);
        if (in_array(str_replace('ldraw/', '', $loc), $parts)) $uzip->addFromString($loc, $contents);
      }  
    }
    
    foreach($ldrawfiles as $file) {
      $contents = $file->get();
      if ($file->getMime() == 'text/plain') $contents = FileUtils::unix2dos($contents);
      $zip->addFromString('ldraw', $contents);
      $uzip->addFromString('ldraw', $contents);
    }
    
    $zip->addFromString("ldraw/models/Notes{$release->short}CA.txt", FileUtils::unix2dos($notes));
    $uzip->addFromString("ldraw/models/Notes{$release->short}CA.txt", FileUtils::unix2dos($notes));
    
    $zip->close();
    $uzip->close();

  }
}
