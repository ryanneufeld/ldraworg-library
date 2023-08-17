<?php

namespace App\Http\Controllers\Part;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartReleaseCreateStep1Request;
use App\Http\Requests\PartReleaseCreateStep2Request;
use App\Models\Part;

class PartReleaseController extends Controller
{
    public function __construct(
        protected \App\LDraw\Check\PartChecker $checker
    ) {}

    protected function create() {
        $this->authorize('create', PartRelease::class);
        $parts = Part::unofficial()->where('vote_sort', 1)->orderBy('part_type_id')->orderBy('filename')->get();
        $results = [];
        foreach($parts as $part) {
            $part->load('descendants', 'ancestors');
            $check = $this->checker->checkCanRelease($part);
            $warnings = [];//$this->checker->historyEventsCrossCheck($part);
            if (isset($part->category) && $part->category->category == "Minifig") {
                $warnings[] = "Check Minifig category: {$part->category->category}";
            }
            $results[] = compact('part', 'check', 'warnings');
        }
        return view('tracker.release.create', ['parts' => $results]);
    }

    protected function createStep2(PartReleaseCreateStep1Request $request) {
        $this->authorize('create', PartRelease::class);
        $data = $request->validated();
        $filelist = [];
        $sdisk = config('ldraw.staging_dir.disk');
        $spath = config('ldraw.staging_dir.path');

        if (Storage::disk($sdisk)->exists("$spath/ldraw")) {
            Storage::disk($sdisk)->deleteDirectory("$spath/ldraw");
        }
        
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
        $parts = new \Illuminate\Database\Eloquent\Collection();
        foreach($release_parts as $part) {
            $parts = $part->allParents();
        }
        $parts = $parts->diff(Part::whereIn('id', $data['ids']));

        Bus::batch([
            [
                new \App\Jobs\Release\MakePartRelease($release_parts, Auth::user()),
                new \App\Jobs\Release\PostReleaseCleanup($parts),
            ]
        ])->then(function ($batch) {
        })->dispatch();
        
        return redirect()->route('tracker.activity');
    }
  
}
