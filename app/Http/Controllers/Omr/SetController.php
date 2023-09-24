<?php

namespace App\Http\Controllers\Omr;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Omr\Set;

class SetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('omr.list');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function show(Set $set)
    {
        $set->load('models');
        return view('omr.show', compact('set'));
    }
}
