<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\LDraw\LibrarySearch;

class SearchController extends Controller
{
  public function partsearch(Request $request) {
    $input = $request->all();
    if (!empty($input['s']) && is_string($input['s'])) {
      $scope = in_array($input['scope'] ?? '', ['filename', 'description', 'header', 'file'], true) ? $input['scope'] : 'header';
      if ($request->expectsJson()) {
        $search = LibrarySearch::partSearch($scope, $input['s'], true);
        return response()->json($search);
      }
      else {
        $search = LibrarySearch::partSearch($scope, $input['s']);
        return view('tracker.search', $search);
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
