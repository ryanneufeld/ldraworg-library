<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

use App\Models\User;
use App\Models\Part;
use App\Models\PartType;
use App\Models\PartRelease;
use App\Models\PartHistory;
use App\Models\PartEvent;
use App\Models\PartEventType;
use App\Models\Vote;

use App\LDraw\FileUtils;
use App\LDraw\PartCheck;
use App\LDraw\LibraryOperations;

use App\Http\Requests\PartSubmitRequest;
use App\Http\Requests\PartHeaderEditRequest;

use App\Jobs\UpdateZip;

use App\Rules\ValidMove;


class UnofficialPartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $parts = Part::unofficial()->with('type')->
        where('description', '<>', 'Missing')->
        orderBy('vote_sort')->
        orderBy('description')->
        lazy();
      return view('tracker.list',[
        'parts' => $parts,
      ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $this->authorize('create', Part::class);
      
      return view('tracker.submit');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PartSubmitRequest $request)
    {
      $this->authorize('create', Part::class);
      $filedata = $request->safe()->all();
      $user = User::find($filedata['user_id']);
      $pt = PartType::find($filedata['part_type_id']);
      $parts = LibraryOperations::addFiles($filedata['partfile'], $user, $pt);
      return view('tracker.postsubmit', ['parts' => $parts]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function show($part)
    {
      if (is_numeric($part)) {
        $p = Part::find($part);
      }
      else {
        $p = Part::findUnofficialByName($part);
      }
      if (!isset($p)) abort(404);
      $p->load('events','history','subparts','parents');
      return view('tracker.show',[
        'part' => $p, 
     ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function edit(Part $part)
    {
      $this->authorize('edit', $part);
      $rows = count(explode("\n", $part->header));
      return view('tracker.edit', ['part' => $part, 'rows' => $rows]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function update(PartHeaderEditRequest $request, Part $part)
    {
      $this->authorize('edit', $part);
      $data = $request->safe()->all();
      
      if ($part->header != $data['h']) {
        // Update the header in the db
        $part->fillFromText(FileUtils::cleanHeader($data['h']), true);
        
        // Post an event
        PartEvent::createFromType('edit', Auth::user(), $part);
        
        UpdateZip::dispatch($part->filename, $part->get());
      }        
      return redirect()->route('tracker.show', [$part])->with('status','Header update successful');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function destroy(Part $part)
    {
      $this->authorize('delete', $part);
    }
    
    public function weekly(Request $request) {
      
      $parts = Part::with('type')->
        where('official_part_id', null)->
        where(function (Builder $query) {
          $query->orWhereRelation('type', 'type', 'Part')->orWhereRelation('type', 'type', 'Shortcut');
        });
      if ($request->has('order') && $request->input('order') == 'asc') {
        $part = $parts->oldest();
      } 
      else {
        $part = $parts->latest();
      }
      return view('tracker.weekly', ['parts' => $parts->get()]);
    }
    
    public function move(Part $part, Request $request) {
/*      
      $this->authorize('edit', $part);
      $validated = $request->validate([
        'new_filename' => [
          'required',
          'string'
          function ($attribute, $value, $fail) {
            if (!PartCheck::libraryApprovedName("0 Name: $value")) {
              $fail('partcheck.name.invalidchars')->translate();
            }
          },
          function ($attribute, $value, $fail) {
            if (Part::findByName) {
              $fail('partcheck.name.invalidchars')->translate();
            }
          },
        ],
      ]);
      if (!part->unofficial) {
        
      }
      $oldfilename = $part->filename;
      $part->filename = $part->$libfolder . '/' . $part->type->folder . $validated['new_filename'];
*/      
    }
}
