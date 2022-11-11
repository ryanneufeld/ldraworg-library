<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileEditController extends Controller
{
  public function show(Request $request) {
    if ($request->filled('editfile')) {
      $filetext = file_get_contents('/var/www/library.ldraw.org/ldraworg-library/' . $request->input('editfile'));
    }
    else {
      $filetext = '';
    }
    return view('edit', ['filetext' => $filetext]);
  }

  public function save(Request $request) {
		file_put_contents('/var/www/library.ldraw.org/ldraworg-library/' . $request->input('file'), $request->input('text'));
    return true;
  }
}
