<?php

namespace App\Http\Controllers\Part;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartReleaseCreateStep1Request;
use App\Http\Requests\PartReleaseCreateStep2Request;
use App\Models\Part;

use App\LDraw\LDrawFileValidate;
use App\LDraw\LibraryOperations;

class PartReleaseController extends Controller
{
  protected function create() {
    $this->authorize('create', PartRelease::class);
    $parts = Part::unofficial()->where('vote_sort', 1)->orderBy('part_type_id')->orderBy('filename')->get();
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
    return view('tracker.release.create', ['parts' => $results]);
  }

  protected function createStep2(PartReleaseCreateStep1Request $request) {
    $this->authorize('create', PartRelease::class);
    $data = $request->validated();
    $filelist = [];
    $sdisk = config('ldraw.staging_dir.disk');
    $spath = config('ldraw.staging_dir.path');
    if (Storage::disk($sdisk)->exists("$spath/ldraw"))
      Storage::disk($sdisk)->deleteDirectory("$spath/ldraw");
    foreach ($data['ldrawfiles'] ?? [] as $file) {
      $filename = "$spath/ldraw/" . $file->getClientOriginalName();
      Storage::disk($sdisk)->put($filename, $file->get());
      $filelist[] = Storage::disk($sdisk)->url($filename);
    }
    return view('tracker.release.create2', ['parts' => Part::whereIn('id', $data['ids'])->lazy(), 'files' => $filelist]);
  }

  protected function store(PartReleaseCreateStep2Request $request) {
    $this->authorize('store', PartRelease::class);
    $data = $request->validated();
    $parts = new \Illuminate\Database\Eloquent\Collection;
    foreach(Part::whereIn('id', $data['ids'])->lazy() as $part) {
      $part->allParents($parts, true);
    }  
    $parts = $parts->diff(Part::whereIn('id', $data['ids']));

    Bus::batch([[
      new \App\Jobs\Release\MakePartRelease($data['ids'], Auth::user()),
      new \App\Jobs\UpdateSubparts,
      new \App\Jobs\Release\PostReleaseCleanup($parts),
    ]])->then(function ($batch) {
    })->dispatch();
    
    return redirect()->route('tracker.activity');
  }
  
}
