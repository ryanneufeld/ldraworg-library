<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Redis\Connections\PhpRedisClusterConnection;
use Illuminate\Support\Facades\Auth;

class CaConfirmController extends Controller
{
  public function edit() {
    return view('tracker.confirmCA');
  }

  public function update() {
    $user = Auth::user();
    $user->license()->associate(\App\Models\PartLicense::defaultLicense());
    $user->save();
    $redirect = session('ca_route_redirect');
    return redirect(route($redirect));
  }

}
