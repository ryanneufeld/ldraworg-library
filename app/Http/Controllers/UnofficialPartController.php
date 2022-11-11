<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\Part;
use App\Helpers\PartsLibrary;
use App\Http\Requests\PartSubmitRequest;

class UnofficialPartController extends Controller
{
    public function __construct()
    {
      //$this->authorizeResource(Part::class, 'part');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $parts = PartsLibrary::unofficialParts();

      if ($request->has('status') && is_numeric($request->input('status'))) {
        $parts = $parts->where('vote_sort', $request->input('status'));
      }
      return view('library.tracker.list',['parts' => $parts->sortBy('vote_sort')->lazy()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      Log::debug('entered create');
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
      $filedata = $request->all();
      
      $file = ''; //file_get_contents($request->partfile->getRealPath());
      return view('tracker.aftersubmit', ['filedata' => print_r($filedata, true), 'file' => $file]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function show(Part $part)
    {
      if (!$part->unofficial) return redirect()->route('library.official.show',$part->id);
      $part->load(['subparts', 'parents']);
      $part->updateUncertifiedSubpartsCache();
      return view('library.tracker.show',['part' => $part]);
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
