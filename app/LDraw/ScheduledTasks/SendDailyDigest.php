<?php

namespace App\LDraw\ScheduledTasks;

use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Models\PartEvent;

use App\Mail\DailyDigest;

class SendDailyDigest {
  public function __invoke(\DateTime $date): void {
    $next = date_add(clone $date, new \DateInterval('P1D'));
    foreach (User::all() as $user) {
      if ($user->hasRole(['Legacy User', 'Synthetic User']) || $user->name == 'PTadmin') continue;

      $events = PartEvent::whereBetween('created_at', [$date, $next])
        ->whereIn('part_id', $user->notification_parts->pluck('id'))->get();
      if ($events->count() > 0)
        Mail::to($user)->send(new DailyDigest($date, $events));         
    }
  }  
}
