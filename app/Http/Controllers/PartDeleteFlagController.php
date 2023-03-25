<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Part;

class PartDeleteFlagController extends Controller
{
  public function store(Part $part, Request $request) {
    if ($request->user()->cannot('part.flag.delete')) {
      abort(403);
    }

    $part->delete_flag = !$part->delete_flag;
    $part->save();
    return back();
  }
}
