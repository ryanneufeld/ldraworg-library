<?php

namespace App\Http\Controllers\Part;

use App\Http\Controllers\Controller;
use App\LDraw\Check\PartChecker;
use App\Models\Part;

class NextReleaseController extends Controller
{
    public function __invoke() {
        $parts = Part::with('descendants', 'ancestors')
            ->unofficial()
            ->where('vote_sort', 1)
            ->orderBy('part_type_id')
            ->orderBy('filename')
            ->get()
            ->reject(function (Part $part) {
                $check = app(PartChecker::class)->checkCanRelease($part);
                return !$check['can_release'];
            });
        return view('part.nextrelease', compact('parts'));
    }
}
