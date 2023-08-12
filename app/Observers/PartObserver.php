<?php

namespace App\Observers;

use App\Events\PartDeleted;
use Illuminate\Support\Facades\Auth;
use App\Models\Part;

class PartObserver
{
    /**
     * Handle the Part "deleting" event.
     */
    public function deleting(Part $part): void
    {
        $part->putDeletedBackup();
        $part->deleteRelationships();
        PartDeleted::dispatch(Auth::user(), $part->filename, $part->description);
    }
}
