<?php

namespace App\Listeners;

use App\Events\PartSubmitted;
use App\Models\Part;
use App\Models\PartEvent;
use App\Models\User;
use Illuminate\Events\Dispatcher;

class PartEventSubscriber
{
    public function storeSubmitPartEvent(PartSubmitted $event)
    {
        PartEvent::create([
            'part_event_type_id' => \App\Models\PartEventType::firstWhere('slug', 'submit')->id,
            'user_id' => $event->user->id,
            'part_id' => $event->part->id,
            'comment' => $event->comment,
        ]);
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            PartSubmitted::class => 'storeSubmitPartEvent',
        ];
    }
}