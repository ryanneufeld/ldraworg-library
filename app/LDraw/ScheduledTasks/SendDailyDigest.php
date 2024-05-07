<?php

namespace App\LDraw\ScheduledTasks;

use Illuminate\Support\Facades\Mail;

use App\Models\User;

use App\Mail\DailyDigest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SendDailyDigest {
    public function __invoke(): void {
        $users = User::whereHas('notification_parts', fn (Builder $q) =>
            $q->whereHas('events', fn (Builder $qu) => $qu->unofficial()->whereBetween('created_at', [Carbon::yesterday(), Carbon::today()]))
        )->get();
        foreach ($users as $user) {
            if ($user->is_legacy || $user->is_synthetic || $user->is_ptadmin) {
                continue;
            } 
            Mail::to($user)->send(new DailyDigest($user));
        }
    }  
}
