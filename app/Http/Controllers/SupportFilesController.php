<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\LDraw\SupportFiles;

class SupportFilesController extends Controller
{
    public function categories() {
      return response(SupportFiles::categoriesText())->header('Content-Type','text/plain');
    }

    public function librarycsv() {
      return response(SupportFiles::libaryCsv())->header('Content-Type','text/plain; charset=utf-8');
    }

    public function ptreleases(Request $request) {
      $output = $request->get('output');
      $releases = SupportFiles::ptreleases($request->get('output'), $request->get('type'), $request->get('fields'));
      if ($output == 'TAB') {
        return response($releases)->header('Content-Type','text/plain');
      }
      else {
        return response($releases)->header('Content-Type','application/xml');    
      }    
    }
}
