<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Models\Part;
use App\Models\PartType;
use App\Models\PartRelease;
use App\Models\PartHistory;
use App\Helpers\PartsLibrary;
use App\LDraw\FileUtils;
use App\Http\Requests\PartSubmitRequest;
use App\Jobs\RenderFile;

class UnofficialPartController extends Controller
{
    public function __construct()
    {
      $this->authorizeResource(Part::class, 'part');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $unof = PartRelease::unofficial()->id;
      return view('tracker.list',[
        'parts' => Part::with(['votes','type'])->where('description', '<>', 'Missing')->
          where('part_release_id', $unof)->
          orderBy('part_type_id')->
          orderBy('description')->
          lazy(),
      ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($dev = null)
    {
      return view('tracker.submit', ['dev' => $dev]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PartSubmitRequest $request)
    {
      $filedata = $request->all();
      if (!empty($filedata['dev'])) {
        foreach($filedata['partfile'] as $file) {
          $filename = basename(strtolower($file->getClientOriginalName()));
          
          $pt = PartType::find($filedata['part_type_id']);

          $upart = Part::findByName($pt->folder . $filename, true);
          $opart = Part::findByName($pt->folder . $filename);

          // Unofficial file exists
          if (isset($upart)) {
            // Save missing state before it is overridden
            $wasmissing = $upart->description == 'Missing';
            // Move old version to timestamped file
            Storage::disk('local')->move('library/unofficial/' . $upart->filename, 'library/backups/' . $upart->filename . '.' . time());
            if ($upart->isTexmap()) {
              // If the submitter is not the author and has not edited the file before, add a history line
              if ($upart->user_id <> $filedata['user_id'] && !empty($upart->history()->where('user_id', $filedata['user_id'])->get()))
                PartHistory::create(['user_id' => $filedata['user_id'], 'part_id' => $upart->id, 'comment' => 'edited']);
              $file->storeAs('library/unofficial/' . $upart->filename, 'local');
            }
            else {
              // Update existing part
              $text = FileUtils::cleanFileText($file->get(), true, true);
              $upart->fillFromText($text, true, true);
            }
            if ($wasmissing) {
              foreach($upart->parents() as $p) $p->updateUncertifiedSubpartsCache();
            }  
          }
          else {
            if ($file->getMimeType() == 'image/png') {
              // Create a new texmap part
              $upart = Part::create([
                'user_id' => $filedata['user_id'],
                'part_release_id' => PartRelease::unofficial(),
                'part_license_id' => PartLicense::defaultLicense()->id,
                'filename' => $pt->folder . $filename,
                'description' => $pt->name . ' ' . $filename,
                'part_type_id' => $pt->id,
              ]);
              $file->storeAs('library/unofficial/' . $upart->filename, 'local');
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
            $upart->official_part->associate($opart);
            $opart->unofficial_part->associate($upart);
          }  
          $partids[] = $upart->id;
        }
        return view('tracker.partsub', ['parts' => Part::whereIn('id', $partids)->get()]);
      }  
      elseif ($request->hasFile('partfile')) {
        foreach($request->file('partfile') as $file) {
          if ($file->getMimetype() == 'text/plain') {
            $text = $file->get();
            $headers[] =
              [
                'filename' => basename(strtolower($file->getClientOriginalName())), 
                'text' => FileUtils::cleanFileText($text, true, true),
              ];
          }
        }
      }
      return view('tracker.aftersubmit', ['files' => $headers]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function show($part)
    {
      $p = Part::find($part) ?? Part::findByName($part, true);
      return view('tracker.show',[
        'part' => $p, 
        'usubparts' => $p->subparts()->whereRelation('release','short','unof')->get(),
        'uparents' => $p->parents()->whereRelation('release','short','unof')->get(),
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Part $part)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function destroy(Part $part)
    {
        //
    }
}
