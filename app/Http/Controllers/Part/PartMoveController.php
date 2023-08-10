<?php

namespace App\Http\Controllers\Part;

use App\Events\PartRenamed;
use App\Events\PartSubmitted;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Part;
use App\Models\PartType;
use App\Http\Requests\PartMoveRequest;
use App\LDraw\PartManager;
use App\Models\PartHistory;

class PartMoveController extends Controller
{
    public function __construct(
        public PartManager $manager
    ) {}

    public function edit(Part $part) {    
        $this->authorize('update', $part);
        return view('tracker.move', ['part' => $part]);
    }

    public function update(Part $part, PartMoveRequest $request) {    
        $this->authorize('update', $part);
        $validated = $request->validated();
        $newType = PartType::find($validated['part_type_id']);
        $newName = basename($validated['newname'], ".{$part->type->format}");
        $newName .= ".{$newType->format}";
        if ($part->isUnofficial()) {
            $oldname = $part->filename;
            $this->manager->movePart($part, $newName, $newType);
            PartRenamed::dispatch($part, Auth::user(), $oldname, $part->filename);
        } else {
            $upart = Part::unofficial()->where('filename', "{$newType->folder}$newName")->first();
            if (is_null($upart)) {
                $upart = $this->manager->copyOfficialToUnofficialPart($part);
                PartHistory::create([
                    'part_id' => $upart->id,
                    'user_id' => Auth::user()->id,
                    'comment' => 'Moved from ' . $part->name(),
                ]);
                $upart->refresh();
                $this->manager->movePart($upart, $newName, $newType);
                PartSubmitted::dispatch($upart, Auth::user());
            }
            $mpart = $this->manager->addMovedTo($part, $upart);
            $mpart->official_part_id = $part->id;
            $part->unofficial_part_id = $mpart->id;
            $part->save();
            $mpart->save();
            PartSubmitted::dispatch($mpart, Auth::user());
        }
        return redirect()->route('tracker.show', [$part])->with('status', 'Move successful');
    }
}
