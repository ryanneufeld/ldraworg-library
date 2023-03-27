<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Part;

class UserNotificationController extends Controller
{
  public function store(Part $part) {
    if (Auth::check()) {
      Auth::user()->togglePartNotification($part);
    }
    return back();
  }
}
