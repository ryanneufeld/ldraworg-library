<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Part;

class SuffixSearchController extends Controller
{
    public function index(Request $request) {
      $input = $request->all();
      if (!empty($input['s']) && is_string($input['s'])) {
        if (strpos($input['s'], '.dat') === false) $input['s'] .= '.dat';
        $basepart = Part::findOfficialByName($input['s'], true) ?? Part::findUnofficialByName($input['s'], true);
        if (!empty($basepart)) {
          $fn = pathinfo($basepart->filename, PATHINFO_FILENAME);
          return view('search.suffix', [
            'patterns' => Part::patterns($fn)->orderBy('filename')->get(),
            'composites' => Part::composites($fn)->orderBy('filename')->get(),
            'stickers' => Part::stickerShortcuts($fn)->orderBy('filename')->get(), 
            'basepart' => $basepart]);
        } 
      }
      return view('search.suffix');        
    }  
}
