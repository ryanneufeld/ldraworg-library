<?php

namespace App\LDraw\ScheduledTasks;

use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Models\PartEvent;

use App\Mail\DailyDigest;

class SendDailyDigest {
    public function __construct(
        public \DateTime $date
    ) {}
    
    public function __invoke(): void {
        $next = date_add(clone $this->date, new \DateInterval('P1D'));
        foreach (User::all() as $user) {
            if ($user->is_legacy || $user->is_synthetic || $user->is_ptadmin) {
                continue;
            } 
            $events = PartEvent::whereBetween('created_at', [$this->date, $next])
                ->whereIn('part_id', $user->notification_parts->pluck('id'))->oldest()->get();
            if ($events->count() > 0) {
                Mail::to($user)->send(new DailyDigest($this->date, $events));
            }
        }
    }  
}
