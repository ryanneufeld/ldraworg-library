<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\Part;
use App\Models\PartEvent;

class PartObserver
{
    /**
     * Handle the Part "deleting" event.
     */
    public function deleting(Part $part): void
    {
        $part->putDeletedBackup();
        $part->deleteRelationships();
        PartEvent::create([
            'part_event_type_id' => \App\Models\PartEventType::firstWhere('slug', 'delete')->id,
            'user_id' => Auth::user()->id,
            'part_release_id' => null,
            'deleted_filename' => $part->filename,
            'deleted_description' => $part->description,
        ]);
    }
}
