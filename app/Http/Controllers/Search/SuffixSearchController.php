<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Part;

class SuffixSearchController extends Controller
{
    public function __invoke(Request $request) {
      $input = $request->all();
      if (!empty($input['s']) && is_string($input['s'])) {
        if (strpos($input['s'], '.dat') === false) $input['s'] .= '.dat';
        $bp = $input['s'];
        $basepart = Part::official()->name($bp)->first() ?? Part::unofficial()->name($bp)->first();
        if (!empty($basepart)) {
          $fn = pathinfo($basepart->filename, PATHINFO_FILENAME);
        } else {
          preg_match(config('ldraw.patterns.basepart'), $bp, $matches);
          $fn = $matches[1] ?? '';
        }
        $patterns = Part::patterns($fn)->orderBy('filename')->get();
        $composites = Part::composites($fn)->orderBy('filename')->get();
        $stickers = Part::stickerShortcuts($fn)->orderBy('filename')->get();
        return view('search.suffix', compact('patterns','composites','stickers','basepart','fn'));
      }
      return view('search.suffix');        
    }  
}
