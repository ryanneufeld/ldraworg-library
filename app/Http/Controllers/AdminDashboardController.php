<?php

namespace App\Http\Controllers;

use App\Models\Part;

class AdminDashboardController extends Controller
{
  public function __construct() {
      $this->middleware('auth');
  }

  public function index() {
    $delete_flags = Part::where('delete_flag', true)->orderby('filename')->get();
    $manual_hold_flags = Part::where('manual_hold_flag', true)->orderby('filename')->get();
    return view('admin.dashboard', compact('delete_flags', 'manual_hold_flags'));
  }
  
}
