<?php

namespace App\Http\Controllers\Part;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Part;

class DatDiffController extends Controller
{
    public function index(Request $request) {
        $parts = Part::orderBy('filename')->pluck('filename', 'id');
        return view('tracker.diff', compact('parts'));
    }

    public function show(Request $request, Part $part, Part $part2) {
        return;
    }
}
