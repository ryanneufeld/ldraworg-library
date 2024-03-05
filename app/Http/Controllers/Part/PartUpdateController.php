<?php

namespace App\Http\Controllers\Part;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\PartRelease;

class PartUpdateController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('output') && in_array(strtolower($request->query('output')), ['xml', 'tab'])) {
            return $this->ptreleases($request);
        }
        if ($request->has('latest')) {
            $releases = PartRelease::current();
        }
        else {
            $releases = PartRelease::latest()->get();
        }
        return view('tracker.release.index', ['releases' => $releases , 'latest' => $request->has('latest')]);
    }

    public function view(PartRelease $release, Request $request)
    {
        return view('tracker.release.view', ['release' => $release]);
    }

}
