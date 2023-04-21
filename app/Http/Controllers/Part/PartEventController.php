<?php

namespace App\Http\Controllers\Part;

use App\Http\Controllers\Controller;

use App\Models\PartEvent;

class PartEventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      return view('tracker.activity');
    }
}
