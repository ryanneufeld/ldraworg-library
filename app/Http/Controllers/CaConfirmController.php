<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\UserChangePartUpdate;

class CaConfirmController extends Controller
{
  public function edit() {
    return view('tracker.confirmCA');
  }

  public function update() {
    $user = Auth::user();
    $user->license()->associate(\App\Models\PartLicense::default());
    $user->save();
    UserChangePartUpdate::dispatch($user);
    $redirect = session('ca_route_redirect');
    return redirect(route($redirect));
  }

}
