<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Part;

class UserNotificationController extends Controller
{
  public function store(User $user, Part $part, Request $request) {
    if (!is_null($request->user()) && $request->user()->id == $user->id) {
      $user->togglePartNotification($part);
    }
    return back();
  }
}
