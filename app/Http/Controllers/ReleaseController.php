<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;

use App\Models\Part;
use App\Models\PartRelease;

use App\LDraw\LDrawFileValidate;
use App\LDraw\LibraryOperations;

class ReleaseController extends Controller
{
  public function index(Request $request)
  {
    if ($request->has('latest')) {
      $releases = PartRelease::current();
    }
    else {
      $releases = PartRelease::where('short', '<>', 'unof')->latest()->get();
    }
    return view('tracker.release.index', ['releases' => $releases , 'latest' => $request->has('latest')]);
  }

  public function view(PartRelease $release, Request $request)
  {
    return view('tracker.release.view', ['release' => $release]);
  }

  public function create(Request $request, $step = null) {
    $this->authorize('create', PartRelease::class);
    set_time_limit(0);
    switch($step) {
      case 3:
        $validated = $request->validate([
          'ids.*' => 'required|integer',
          'approve' => 'required:accepted',
        ]);
        set_time_limit(30);
        return $this->doStep3($validated['ids'], $request->file('ldrawfile'));
        break;
      case 2:
        $validated = $request->validate([
          'ldrawfiles.*' => 'sometimes|file',
          'ids.*' => 'required|integer',
        ]);
        set_time_limit(30);
        return $this->doStep2($validated['ids'], $validated['ldrawfiles'] ?? []);
        break;
      case 1:
      default:
        set_time_limit(30);
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
    $filelist = [];
    $sdisk = config('ldraw.staging_dir.disk');
    $spath = config('ldraw.staging_dir.path');
    if (Storage::disk($sdisk)->exists("$spath/ldraw"))
      Storage::disk($sdisk)->deleteDirectory("$spath/ldraw");
    foreach ($ldrawfiles as $file) {
      $filename = "$spath/ldraw/" . $file->getClientOriginalName();
      Storage::disk($sdisk)->put($filename, $file->get());
      $filelist[] = Storage::disk($sdisk)->url($filename);
    }
    return view('tracker.release.create.step2', ['parts' => Part::whereIn('id', $ids)->lazy(), 'files' => $filelist]);
  }

  protected function doStep3($ids) {
    foreach(Part::whereIn('id', $ids)->lazy() as $part) {
      LibraryOperations::getAllParentIds($part, $unf_render_list, true);
    }  
    $unf_render_list = array_diff($unf_render_list, $ids);

    Bus::batch([[
      new \App\Jobs\Release\MakePartRelease($ids, Auth::user()),
      new \App\Jobs\Release\MakeReleaseZip,
      new \App\Jobs\UpdateSubparts(false),
      new \App\Jobs\UpdateUncertifiedSubparts(true),
      new \App\Jobs\Release\PostReleaseCleanup($unf_render_list),
    ]])->then(function ($batch) {
    })->dispatch();
    return redirect()->route('tracker.activity');
  }
  
}
