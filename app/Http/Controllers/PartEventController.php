<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PartEvent;
use App\Models\Part;

class PartEventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $events = PartEvent::with(['part', 'user', 'part_event_type'])->orderBy('created_at','desc')->cursorPaginate(20);
      $summary = Part::whereRelation('release','short','unof')->pluck('vote_sort')->countBy()->all();
      return view('tracker.activity',['events' => $events, 'summary' => $summary]);
    }
}
