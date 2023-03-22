<?php

namespace App\Observers;

use App\Models\Part;

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
      $part->history()->delete();
      $part->votes()->delete();
      $part->events()->delete();
      $part->help()->delete();
      $part->body->delete();
      $part->keywords()->sync([]);
      $part->subparts()->sync([]);
      $part->notification_users()->sync([]);
    }

    /**
     * Handle the Part "restored" event.
     */
    public function restored(Part $part): void
    {
        //
    }

    /**
     * Handle the Part "force deleting" event.
     */
    public function forceDeleting(Part $part): void
    {
    }
}
