<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Part;

class SuffixSearchController extends Controller
{
    public function index(Request $request) {
      $input = $request->all();
      if (!empty($input['s']) && is_string($input['s']) && !empty($input['scope']) && in_array($input['scope'], ['p','c','d'])) {
        if (strpos($input['s'], '.dat') === false) $input['s'] .= '.dat';
        $basepart = Part::findOfficialByName($input['s'], true) ?? Part::findUnofficialByName($input['s'], true);
        if (!empty($basepart)) {
          $fn = pathinfo($basepart->filename, PATHINFO_FILENAME);
          switch($input['scope']) {
            case 'p':
              $parts = Part::patterns($fn)->orderBy('filename')->get();
              $scope = 'Pattern';
              break;
            case 'c':
              $parts = Part::composites($fn)->orderBy('filename')->get();
              $scope = 'Composite';
              break;
            case 'd':
              $parts = Part::stickerShortcuts($fn)->orderBy('filename')->get();
              $scope = 'Sticker Shortcut';
              break;
          }
          return view('search.suffix', ['parts' => $parts, 'basepart' => $basepart, 'scope' => $scope]);
        } 
      }
      return view('search.suffix');        
    }  
}
