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
      $input = $request->all();
      
      $subset = [1 => 'Certified', 2 => 'Needs Admin Review', 3 => 'Needs More Votes', 4 => 'Uncertified Subfiles', 5 => 'Hold'];
      $users = User::whereHas('parts', function (Builder $query) {
        $query->unofficial();
      })->orderBy('name')->pluck('name', 'id')->all();
      $part_types = PartType::pluck('name', 'id')->all();
      $parts = Part::unofficial();
      
      if (isset($input['subset']) && array_key_exists($input['subset'], $subset))
        $parts = $parts->where('vote_sort', $input['subset']);
      if (isset($input['user_id']) && array_key_exists($input['user_id'], $users))
        $parts = $parts->where('user_id', $input['user_id']);
      if (isset($input['part_type_id']) && array_key_exists($input['part_type_id'], $part_types))
        $parts = $parts->where('part_type_id', $input['part_type_id']);
      $parts = $parts->orderBy('vote_sort')->
        orderBy('part_type_id')->
        orderBy('filename')->
        lazy();
      return view('tracker.list',[
        'parts' => $parts,
        'subset' => $subset,
        'users' => $users,
        'part_types' => $part_types,
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
      $user->notification_parts()->syncWithoutDetaching($parts->pluck('id'));
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
      $this->authorize('update', $part);
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
      $this->authorize('update', $part);
      $data = $request->safe()->all();
      
      if ($part->header != $data['h']) {
        // Update the header in the db
        $part->fillFromText(FileUtils::cleanHeader($data['h']), true);
        
        // Post an event
        PartEvent::createFromType('edit', Auth::user(), $part);
        Auth::user()->notification_parts()->syncWithoutDetaching([$part->id]);
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
      
      $parts = Part::with('type')->unofficial()->
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
      $this->authorize('update', $part);
      return view('tracker.move', ['part' => $part]);
    }

    public function domove(Part $part, Request $request) {    
      $this->authorize('update', $part);
      $validated = $request->validate([
        'part_id' => 'required|in:' . $part->id,
        'part_type_id' => 'required|exists:part_types,id',
        'newname' => [new MoveName],
      ]);
      $part->move($validated['newname'], PartType::find($validated['part_type_id']));
      return redirect()->route('tracker.show', [$part])->with('status','Move successful');
    }
}
