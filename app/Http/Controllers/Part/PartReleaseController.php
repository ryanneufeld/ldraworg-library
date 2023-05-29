<?php

namespace App\Http\Controllers\Part;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Http\UploadedFile;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartReleaseCreateStep1Request;
use App\Http\Requests\PartReleaseCreateStep2Request;
use App\Models\Part;

use App\LDraw\LDrawFileValidate;
use App\LDraw\PartCheck;
use App\LDraw\LibraryOperations;

class PartReleaseController extends Controller
{
  protected function create() {
    $this->authorize('create', PartRelease::class);
    $parts = Part::unofficial()->where('vote_sort', 1)->orderBy('part_type_id')->orderBy('filename')->get();
    $results = [];
    foreach($parts as $part) {
//      $text = $part->isTexmap() ? $part->header : $part->get();
      $mime = $part->isTexmap() ? 'image/png' : 'text/plain';
      Storage::fake('local')->put($part->filename, $part->get());
      $partfile = new UploadedFile(Storage::disk('local')->path($part->filename), $part->filename, $mime, null, true);
      $errors = [];
      if ($ferrors = PartCheck::checkFile($partfile)) {
        foreach($ferrors as $error) {
            if (array_key_exists('args', $error)) {
              $errors[] = __($error['error'], $error['args']);
            } else {
              $errors[] = __($error['error']);;
            }    
        }    
      }
      if ($herrors = PartCheck::checkHeader($partfile, ['part_type_id' => $part->part_type_id])) {
        foreach($herrors as $error) {
            if (array_key_exists('args', $error)) {
              $errors[] = __($error['error'], $error['args']);
            } else {
              $errors[] = __($error['error']);;
            }    
        }    
      }

      $warnings = PartCheck::historyEventsCrossCheck($part);

      if (isset($part->category) && $part->category->category == "Minifig") {
        $warnings[] = "Check Minifig category: {$part->category->category}";
      }
      $release = empty($errors) && $part->releasable();
      $results[] = compact('part', 'release', 'errors', 'warnings');
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
    $release_parts = Part::whereIn('id', $data['ids'])->get();
    $parts = new \Illuminate\Database\Eloquent\Collection;
    foreach($release_parts as $part) {
      $part->allParents($parts, true);
    }
    $parts = $parts->diff(Part::whereIn('id', $data['ids']));

    Bus::batch([[
      new \App\Jobs\Release\MakePartRelease($release_parts, Auth::user()),
      new \App\Jobs\UpdateSubparts,
      new \App\Jobs\Release\PostReleaseCleanup($parts),
    ]])->then(function ($batch) {
    })->dispatch();
    
    return redirect()->route('tracker.activity');
  }
  
}
