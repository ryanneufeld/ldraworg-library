<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

use App\Models\User;
use App\Models\Part;
use App\Models\PartType;
use App\Models\PartEvent;

use App\LDraw\FileUtils;
use App\LDraw\LibraryOperations;

use App\Http\Requests\PartSubmitRequest;
use App\Http\Requests\PartHeaderEditRequest;

use App\Rules\MoveName;

use App\Jobs\UpdateZip;

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
      $parts = LibraryOperations::addFiles($filedata['partfile'], $user, $pt, $filedata['comment'] ?? null);
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
    public function editheader(Part $part)
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
    public function doeditheader(PartHeaderEditRequest $request, Part $part)
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
      Storage::disk('library')->move('unofficial/' . $part->filename, 'backups/' . $part->filename . 'deleted');
      $part->delete();
    }
    
    public function weekly(Request $request) {
      
      $parts = Part::with('type')->
        where('official_part_id', null)->
        where(function (Builder $query) {
          $query->orWhereRelation('type', 'type', 'Part')->orWhereRelation('type', 'type', 'Shortcut');
        });
      if ($request->has('order') && $request->input('order') == 'asc') {
        $parts = $parts->oldest();
      } 
      else {
        $parts = $parts->latest();
      }
      return view('tracker.weekly', ['parts' => $parts->get()]);
    }
    
    public function move(Part $part) {    
      $this->authorize('edit', $part);
      return view('tracker.move', ['part' => $part]);
    }

    public function domove(Part $part, Request $request) {    
      $validated = $request->validate([
        'part_id' => 'required|in:' . $part->id,
        'part_type_id' => 'required|exists:part_types,id',
        'newname' => [new MoveName],
      ]);
      $part->move($validated['newname'], PartType::find($validated['part_type_id']));
      return redirect()->route('tracker.show', [$part])->with('status','Move successful');
    }
}
