<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

use App\Models\PartEventType;
use App\Models\Part;
use App\Models\Vote;

class UserDashboardController extends Controller
{
  public function __construct() {
      $this->middleware('auth');
  }

  public function index() {
    $partSort = [['vote_sort','asc'],['type.folder', 'asc'],['description', 'asc']];
    $submits = Part::unofficial()->userSubmits(Auth::user())->get()->sortBy($partSort);
    $votes = Vote::with(['part'])->where('user_id', Auth::user()->id)->get()->sortBy([['vote_type_code','asc'],['part.type.folder', 'asc'],['part.description', 'asc']]);
    $tracked = Auth::user()->notification_parts->sortBy($partSort);
    return view('dashboard.index', compact('submits', 'votes', 'tracked'));
  }
  
}
