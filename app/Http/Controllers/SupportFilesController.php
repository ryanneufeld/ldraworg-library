<?php

namespace App\Http\Controllers;

use App\LDraw\SupportFiles;

class SupportFilesController extends Controller
{
    public function categories() 
    {
        return response(SupportFiles::categoriesText())->header('Content-Type','text/plain');
    }

    public function librarycsv() 
    {
        return response(SupportFiles::libaryCsv())->header('Content-Type','text/plain; charset=utf-8');
    }
}
