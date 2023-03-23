<?php

namespace App\Observers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Part;
use App\Models\PartEvent;

class PartObserver
{
    /**
     * Handle the Part "created" event.
     */
    public function created(Part $part): void
    {
        //
    }

    /**
     * Handle the Part "updated" event.
     */
    public function updated(Part $part): void
    {
        //
    }

    /**
     * Handle the Part "deleted" event.
     */
    public function deleting(Part $part): void
    {
      Storage::disk('local')->put('deleted/library/' . $part->filename . '.' . time(), $part->get());
      $part->history()->delete();
      $part->votes()->delete();
      $part->events()->delete();
      $part->help()->delete();
      $part->body->delete();
      $part->keywords()->sync([]);
      $part->subparts()->sync([]);
      $part->notification_users()->sync([]);

      PartEvent::create([
        'part_event_type_id' => \App\Models\PartEventType::firstWhere('slug', 'delete')->id,
        'user_id' => Auth::user()->id,
        'part_release_id' => \App\Models\PartRelease::unofficial()->id,
        'deleted_filename' => $part->filename,
        'deleted_description' => $part->description,
      ]);
    }
}
