<?php

namespace App\Listeners;

use App\Events\PartHeaderEdited;
use App\Events\PartRenamed;
use App\Events\PartSubmitted;
use App\Models\PartEvent;
use Illuminate\Events\Dispatcher;

class PartEventSubscriber
{
    public function storeSubmitPartEvent(PartSubmitted $event)
    {
        $init_submit = is_null(PartEvent::unofficial()->firstWhere('part_id', $event->part->id));
        PartEvent::create([
            'part_event_type_id' => \App\Models\PartEventType::firstWhere('slug', 'submit')->id,
            'initial_submit' => $init_submit,
            'user_id' => $event->user->id,
            'part_id' => $event->part->id,
            'comment' => $event->comment,
        ]);
    }

    public function storeRenamePartEvent(PartRenamed $event)
    {
        PartEvent::create([
            'part_event_type_id' => \App\Models\PartEventType::firstWhere('slug', 'rename')->id,
            'user_id' => $event->user->id,
            'part_id' => $event->part->id,
            'moved_to_filename' => $event->moved_to,
            'moved_from_filename' => $event->moved_from,
        ]);
    }

    public function storePartHeaderEditEvent(PartHeaderEdited $event)
    {
        PartEvent::create([
            'part_event_type_id' => \App\Models\PartEventType::firstWhere('slug', 'edit')->id,
            'user_id' => $event->user->id,
            'part_id' => $event->part->id,
            'comment' => $event->comment,
        ]);
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            PartSubmitted::class => 'storeSubmitPartEvent',
            PartRenamed::class => 'storeRenamePartEvent',
            PartHeaderEdited::class => 'storePartHeaderEditEvent',
        ];
    }
}