<?php

namespace App\LDraw\ScheduledTasks;

use App\Models\Part;
use App\Models\TrackerHistory;

class UpdateTrackerHistory
{
    public function __invoke(): void
    {
        $data = Part::unofficial()->pluck('vote_sort')->countBy()->all();
        $h = new TrackerHistory;
        $h->history_data = $data;
        $h->save();
    }
}
