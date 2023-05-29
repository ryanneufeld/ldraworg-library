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
      $text = $part->isTexmap() ? $part->header : $part->get();
      $mime = $part->isTexmap() ? 'image/png' : 'text/plain';
      Storage::fake('local')->put($part->filename, $text);
      $partfile = new UploadedFile(Storage::disk('local')->path($part->filename), $part->filename, $mime, null, true);
      $fileerrors = \App\LDraw\PartCheck::checkFile($partfile);
      $headererrors = \App\LDraw\PartCheck::checkHeader($partfile, ['part_type_id' => $part->part_type_id]);
      return !is_null($fileerrors) || !is_null($headererrors);
    });
    return view('part.nextrelease', ['parts' => $parts, 'minor_edits' => $minor_edits]);

  }
}
