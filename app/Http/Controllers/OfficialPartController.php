<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;

class OfficialPartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $parts = Part::official()->lazy();
      return view('official.list',['parts' => $parts]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function show(Part $part, Request $request)
    {
      if ($part->isUnofficial() && !is_null($part->official_part_id)) {
        $part = Part::find($part->official_part_id);
      }
      elseif ($part->isUnofficial() && is_null($part->official_part_id)) {
        abort(404);
      } 
      
      $part->load('events','history','subparts','parents');
      return view('official.show',[
        'part' => $part, 
     ]);
    }

    public function download(Part $part) {
      if ($part->isTexmap()) {
        $header = ['Content-Type' => 'image/png'];
      }
      else {
        $header = ['Content-Type' => 'text/plain'];
      }
      return response()->streamDownload(function() use ($part) { echo $part->get(); }, basename($part->filename), $header);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function edit(Part $part)
    {
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
