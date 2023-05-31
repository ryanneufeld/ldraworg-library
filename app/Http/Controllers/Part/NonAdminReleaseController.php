<?php

namespace App\Http\Controllers\Part;

use App\Http\Controllers\Controller;
use App\Models\Part;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class NonAdminReleaseController extends Controller
{
  public function index() {
    $parts = Part::unofficial()->where('vote_sort', 1)->orderBy('part_type_id')->orderBy('filename')->get();
    $minor_edits = Part::official()->whereNull('unofficial_part_id')->whereNotNull('minor_edit_data')->orderBy('filename')->cursor();
    $parts = $parts->reject(function (Part $part) {
      if (!$part->releasable()) {
        return true;
      }
      $fileerrors = \App\LDraw\PartCheck::checkFile($part->toUploadedFile());
      $headererrors = \App\LDraw\PartCheck::checkHeader($part->toUploadedFile(), ['part_type_id' => $part->part_type_id]);
      return !is_null($fileerrors) || !is_null($headererrors);
    });
    return view('part.nextrelease', ['parts' => $parts, 'minor_edits' => $minor_edits]);

  }
}
