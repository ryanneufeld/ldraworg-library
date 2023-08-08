<?php

namespace App\Http\Controllers\Part;

use App\Http\Controllers\Controller;
use App\Models\Part;

class NonAdminReleaseController extends Controller
{
    public function __construct(
        protected \App\LDraw\Check\PartChecker $checker
    ) {}

    public function __invoke() {
        $parts = Part::with(['parents', 'subparts'])->unofficial()->where('vote_sort', 1)->orderBy('part_type_id')->orderBy('filename')->get();
        $parts = $parts->reject(function (Part $part) {
            $check = $this->checker->checkCanRelease($part);
            return !$check['can_release'];
        });
        return view('part.nextrelease', compact('parts'));
    }
}
