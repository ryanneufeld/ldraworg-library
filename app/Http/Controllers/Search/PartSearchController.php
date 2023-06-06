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
      return view('search.part', ['search' => $input['s']]);
    }
    else {
        return view('search.part');                  
    }
  }
}
