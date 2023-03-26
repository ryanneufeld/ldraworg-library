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
    foreach($user->parts as $part) {
      $oldlid = $part->license->id;
      $part->updateLicense();
      if ($oldlid != $part->license->id) {
        $part->refreshHeader();
        $part->minor_edit_flag = true;
        $part->save();  
      }
    }
    $redirect = session('ca_route_redirect');
    return redirect(route($redirect));
  }

}
