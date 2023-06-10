<?php

namespace App\Http\Controllers\Part;

use App\Http\Controllers\Controller;
use App\Models\Part;

class NonAdminReleaseController extends Controller
{
  public function __construct(
    protected \App\LDraw\PartChecker $checker
  ) {}

  public function __invoke() {
    $parts = Part::with(['parents', 'subparts'])->unofficial()->where('vote_sort', 1)->orderBy('part_type_id')->orderBy('filename')->get();
    $parts = $parts->reject(function (Part $part) {
      $errors = $this->checker->check($part);
      return !is_null($errors) || 
        (!$part->hasCertifiedParent() && $part->type->folder != "parts/" && !is_null($part->official_part_id)) ||
        $part->hasUncertifiedSubparts() ||
        $part->manual_hold_flag;
    });
    return view('part.nextrelease', compact('parts'));

  }
}
