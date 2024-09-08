<?php

namespace App\LDraw\ScheduledTasks;

use App\Mail\DailyDigest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendDailyDigest
{
    public function __invoke(): void
    {
        $users = User::whereHas('notification_parts', fn (Builder $q) => $q->whereHas('events', fn (Builder $qu) => $qu->unofficial()->whereBetween('created_at', [Carbon::yesterday(), Carbon::today()]))
        )->where('is_legacy', false)->where('is_synthetic', false)->where('is_ptadmin', false)->get();
        foreach ($users as $user) {
            if ($user->is_legacy || $user->is_synthetic || $user->is_ptadmin) {
                continue;
            }
            Mail::to($user)->send(new DailyDigest($user));
        }
    }
}
