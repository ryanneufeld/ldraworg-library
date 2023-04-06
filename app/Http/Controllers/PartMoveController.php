<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Part;
use App\Models\PartType;
use App\Models\PartEvent;
use App\Http\Requests\PartMoveRequest;

class PartMoveController extends Controller
{
    public function edit(Part $part) {    
        $this->authorize('update', $part);
        return view('tracker.move', ['part' => $part]);
    }

    public function update(Part $part, PartMoveRequest $request) {    
        $this->authorize('update', $part);
        $validated = $request->validated();
        $oldname = $part->name();
        $newtype = PartType::find($validated['part_type_id']);
        if (empty($newtype) || $newtype->folder == $part->type->folder) $newtype = clone $part->type;
        $newname = pathinfo($validated['newname'], PATHINFO_FILENAME) . '.' . $newtype->format;
        $part->move($newname, $newtype);
        if ($part->isUnofficial()) {
            PartEvent::create([
                'part_event_type_id' => \App\Models\PartEventType::firstWhere('slug', 'rename')->id,
                'user_id' => Auth::user()->id,
                'comment' => "part $oldname was renamed to {$part->name()}",
                'part_release_id' => \App\Models\PartRelease::unofficial()->id,
                'part_id' => $part->id,
            ]);
       }
        return redirect()->route('tracker.show', [$part])->with('status','Move successful');
    }
}
