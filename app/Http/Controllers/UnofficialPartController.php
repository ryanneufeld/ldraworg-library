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
use App\Helpers\PartsLibrary;
use App\LDraw\FileUtils;
use App\Http\Requests\PartSubmitRequest;
use App\Http\Requests\PartHeaderEditRequest;
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
      $unof = PartRelease::unofficial()->id;
      
      $parts = Part::with('type')->
        where('description', '<>', 'Missing')->
        where('part_release_id', $unof)->
        orderBy('part_type_id')->
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
      foreach($filedata['partfile'] as $file) {
        $filename = basename(strtolower($file->getClientOriginalName()));
        
        $pt = PartType::find($filedata['part_type_id']);
        $upart = Part::findByName($pt->folder . $filename, true);
        $opart = Part::findByName($pt->folder . $filename);
        $user = User::find($filedata['user_id']);
        $votes_deleted = false;

        // Unofficial file exists
        if (isset($upart)) {
          $init_submit = false;
          if ($upart->isTexmap()) {
            // If the submitter is not the author and has not edited the file before, add a history line
            if ($upart->user_id <> $user->id && empty($upart->history()->whereFirst('user_id', $user->id)))
              PartHistory::create(['user_id' => $user->id, 'part_id' => $upart->id, 'comment' => 'edited']);
            $upart->put($file->get());
          }
          else {
            // Update existing part
            $text = FileUtils::cleanFileText($file->get(), true, true);
            $upart->fillFromText($text, true, true);
          }
          if ($upart->votes->count() > 0) $votes_deleted = true;
          foreach($upart->votes as $v) {
            $v->delete();
          }              
        }
        // Create a new part
        else {
          $init_submit = true;
          if ($file->getMimeType() == 'image/png') {
            // Create a new texmap part
            $upart = Part::createTexmap([
              'user_id' => $user->id,
              'part_release_id' => PartRelease::unofficial(),
              'part_license_id' => PartLicense::defaultLicense()->id,
              'filename' => $pt->folder . $filename,
              'description' => $pt->name . ' ' . $filename,
              'part_type_id' => $pt->id,
            ], $file->get());
          }
          else {            
            // Create a new part
            $text = FileUtils::cleanFileText($file->get(), true, true);
            $upart = Part::createFromText($text, true, true);
          }  
        }
        
        $upart->updateSubparts(true);
        $upart->updateImage(true);
        
        if (!empty($opart)) {
          $upart->official_part_id = $opart->id;
          $upart->save();
          $opart->unofficial_part_id = $upart->id;
          $opart->save();
        }

        $comment = $filedata['comment'] ?? null;
        PartEvent::createFromType('submit', $user, $upart, $comment, null, null, $init_submit);        

        $partids[] = $upart->id;
        UpdateZip::dispatch($upart->filename, $upart->get());
      }
      return view('tracker.postsubmit', ['parts' => Part::whereIn('id', $partids)->get()]);
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
        if (isset($p) && !$p->unofficial) return Redirect::route('official.show', $p->id);
      }
      else {
        $p = Part::findByName($part, true);
      }
      if (!isset($p)) abort(404);

      return view('tracker.show',[
        'part' => $p->load('events','history','subparts','parents'), 
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
        // Copy a backup to backups
        Storage::disk('local')->copy('library/unofficial/' . $part->filename, 'library/backups/' . $part->filename . '.' . time());
        
        // Update the header in the db
        $part->fillFromText(FileUtils::cleanHeader($data['h']), false, true, true);
        
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
      
      $parts = Part::with('type')->where('description', '<>', 'Missing')->
        whereRelation('release', 'short', 'unof')->
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
}
