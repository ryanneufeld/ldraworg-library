<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

use App\Models\PartEventType;
use App\Models\Part;
use App\Models\Vote;

class DashboardController extends Controller
{
  public function __construct() {
      $this->middleware('auth');
  }

  public function index() {
    $submits = Part::whereHas('events', function (Builder $query) {
        $query->whereRelation('part_event_type', 'slug', 'submit')->where('user_id', Auth::user()->id);
      })->get();
    $votes = Vote::where('user_id', Auth::user()->id)->get();
    return view('dashboard.index', ['submits' => $submits, 'votes' => $votes]);
  }
  
}
