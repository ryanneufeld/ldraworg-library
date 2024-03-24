<?php

namespace App\Http\Controllers\Part;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartReleaseCreateStep1Request;
use App\Http\Requests\PartReleaseCreateStep2Request;
use App\Models\Part;
use App\Jobs\MakePartRelease;
use App\LDraw\Check\PartChecker;
use App\Models\PartRelease;
use Illuminate\Database\Eloquent\Collection;

class PartReleaseController extends Controller
{
    public function __construct(
        protected PartChecker $checker
    ) {}

    protected function create() {
        $this->authorize('create', PartRelease::class);
        $results = [];
        Part::unofficial()->where('vote_sort', 1)
            ->orderBy('part_type_id')
            ->orderBy('filename')
            ->chunk(50, function (Collection $parts) use (&$results) {
                foreach($parts as $part) {
                    $check = $this->checker->checkCanRelease($part);
                    $warnings = [];//$this->checker->historyEventsCrossCheck($part);
                    if (isset($part->category) && $part->category->category == "Minifig") {
                        $warnings[] = "Check Minifig category: {$part->category->category}";
                    }
                    $results[] = [
                        'id' => $part->id,
                        'description' => $part->description,
                        'name' => $part->name(),
                        'filename' => $part->filename,
                        'warnings' => $warnings,
                        'check' => $check,
                        'fix' => !is_null($part->official_part),
                        'ft' => $part->votes()->where('vote_type_code', 'T')->count() > 0
                    ];
                }        
            });        
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
        return view('tracker.release.create2', ['parts' => Part::whereIn('id', $data['ids'])->lazy(), 'files' => $filelist, 'count' => count($data['ids'])]);
    }

    protected function store(PartReleaseCreateStep2Request $request) {
        $this->authorize('store', PartRelease::class);
        $data = $request->validated();
        $release_parts = Part::whereIn('id', $data['ids'])->get();
        MakePartRelease::dispatch($release_parts, Auth::user());
        return redirect()->route('tracker.activity');
    }
}
