<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\PartEventType;

class DashboardController extends Controller
{
  public function __construct() {
      $this->middleware('auth');
  }

  public function index() {
    return view('dashboard.index');
  }
  
  public function submits() {
    $events = Auth::user()
      ->part_events()
      ->with('part')
      ->where('part_event_type_id', PartEventType::firstWhere('slug','submit')->id)->get()
      ->sortBy('part.description')
      ->unique('part.id')->values()->all();
    return view('dashboard.submits', ['events' => $events]);
  }

  public function votes() {
    $votes = Auth::user()->votes()->with(['part','type'])->get()->sortBy('part.description');
    return view('dashboard.votes', ['votes' => $votes]);
  }

  public function notifications() {
    return view('dashboard.notifications');
  }
  
}
