<?php

namespace App\Http\Controllers\Search;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\Part;

class PartSearchController extends Controller
{
  public function index(Request $request) {
    $input = $request->all();
    if (!empty($input['s']) && is_string($input['s'])) {
      $scope = in_array($input['scope'] ?? '', ['filename', 'description', 'header', 'file'], true) ? $input['scope'] : 'header';
      $parts = Part::searchPart($input['s'], $scope)->orderBy('filename')->get();
      $uparts = $parts->where('release.short', 'unof');
      $oparts = $parts->where('release.short', '<>', 'unof');
      return view('search.part', ['results' => ['oparts' => $oparts, 'uparts' => $uparts]]);
    }
    else {
        return view('search.part');                  
    }
  }
}
