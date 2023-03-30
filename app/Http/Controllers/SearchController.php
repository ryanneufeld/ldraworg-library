<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\LDraw\LibrarySearch;
use App\Models\Part;
use App\Http\Resources\PartSearchResource;

class SearchController extends Controller
{
  public function partsearch(Request $request) {
    $input = $request->all();
    if (!empty($input['s']) && is_string($input['s'])) {
      $scope = in_array($input['scope'] ?? '', ['filename', 'description', 'header', 'file'], true) ? $input['scope'] : 'header';
      $oparts = Part::search($input['s'], $scope);
      $uparts = Part::search($input['s'], $scope, true);
      $json_limit = config('ldraw.search.quicksearch.limit');
      if ($request->expectsJson()) {
        return ['results' => [
          'oparts' => ['name' => "Official\nParts", 'results' => PartSearchResource::collection($oparts->slice(0, $json_limit))],
          'uparts' => ['name' => "Unofficial\nParts", 'results' => PartSearchResource::collection($uparts->slice(0, $json_limit))],
        ]];
      }
      else {
        return view('tracker.search', ['results' => ['oparts' => $oparts, 'uparts' => $uparts]]);
      }  
    }
    else {
      if ($request->expectsJson()) {
        return response(400);
      }
      else {
        return view('tracker.search');                  
      }
    }
  }

    
  public function suffixsearch(Request $request) {
    $input = $request->all();
    if (!empty($input['s']) && is_string($input['s']) && !empty($input['scope']) && in_array($input['scope'], ['p','c','d'])) {
      return view('tracker.summary', LibrarySearch::suffixSearch($input['s'], $input['scope']));
    }
    else {
      return view('tracker.summary');        
    }
  }  
}
