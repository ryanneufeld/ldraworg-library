<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PartMissingUpdateRequest;
use App\Models\Part;

class PartMissingController extends Controller
{
    public function index(Request $request) {  
        return view('part.missingindex', ['parts' => Part::withoutGlobalScope('missing')->unofficial()->where('description', 'Missing')->whereHas('parents')->get()]);
    }

    public function edit (Part $part) {
        $this->authorize('update', $part);
        return view('part.updatemissing', ['part' => $part]);
    }
  
    public function update (Part $part, PartMissingUpdateRequest $request) {
        $this->authorize('update', $part);
        $validated = $request->validated();
        $new = Part::find($validated['new_part_id']);
        foreach($part->parents as $p) {
            $p->body->body = str_replace($part->name(), $new->name(), $p->body->body);
            $p->body->save();
            $p->updateSubparts(true);
            $p->updateImage(true);
        }
        return redirect()->route('tracker.show', $new)->with('status','Missing part updated');
    }
  }
