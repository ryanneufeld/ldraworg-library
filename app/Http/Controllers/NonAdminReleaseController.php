<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Part;
use App\LDraw\LDrawFileValidate;

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
      $errors = LDrawFileValidate::ValidName($text, basename($part->filename), $part->part_type_id);
    
      // These checks are only valid for non-texmaps
      if (!$part->isTexmap()) {
        $errors = array_merge($errors, LDrawFileValidate::ValidDescription($text));
        $errors = array_merge($errors, LDrawFileValidate::ValidAuthor($text));
        $errors = array_merge($errors, LDrawFileValidate::ValidPartType($text, $part->part_type_id));
        $errors = array_merge($errors, LDrawFileValidate::ValidCategory($text));
        $errors = array_merge($errors, LDrawFileValidate::ValidKeywords($text));
        $errors = array_merge($errors, LDrawFileValidate::ValidHistory($text));
        $errors = array_merge($errors, LDrawFileValidate::ValidLines($text));      
      }
      return !empty($errors);
    });
    return view('part.nextrelease', ['parts' => $parts, 'minor_edits' => $minor_edits]);

  }
}
